<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which is used inside boolean arguments
 */
class BooleanNode extends AbstractNode {

	/**
	 * Characters trimmed from strings used in comparisons
	 */
	const TRIM_CHARACTERS = " \t\n\r\0\x0B'\"";

	/**
	 * List of comparators which are supported in the boolean expression language.
	 *
	 * Make sure that if one string is contained in one another, the longer
	 * string is listed BEFORE the shorter one.
	 * Example: put ">=" before ">"
	 *
	 * @var array
	 */
	static protected $comparators = array('===', '==', '!=', '%', '>=', '>', '<=', '<');

	/**
	 * @var array
	 */
	static protected $groupers = array('&&', '||');

	/**
	 * A regular expression which checks the text nodes of a boolean expression.
	 * Used to define how the regular expression language should look like.
	 *
	 * @var string
	 */
	static protected $booleanExpressionTextNodeCheckerRegularExpression = '/
		^                       # Start with first input symbol
		(?:                     # start repeat
			&&|\|\|             # we allow groupers
			|===|==|!=|%|>=|>|<=|<  # We allow all comparators
			|\s*                # Arbitrary spaces
			|-?                 # Numbers, possibly with the "minus" symbol in front.
				[0-9]+          # some digits
				(?:             # and optionally a dot, followed by some more digits
					\\.
					[0-9]+
				)?
			|\'[^\'\\\\]*       # single quoted string literals with possibly escaped single quotes
				(?:
					\\\\.       # escaped character
					[^\'\\\\]*  # unrolled loop following Jeffrey E.F. Friedl
				)*\'
			|"[^"\\\\]*         # double quoted string literals with possibly escaped double quotes
				(?:
					\\\\.       # escaped character
					[^"\\\\]*   # unrolled loop following Jeffrey E.F. Friedl
				)*"
		)*
		$/x';

	/**
	 * Stack of expression nodes to be evaluated
	 *
	 * @var NodeInterface[]
	 */
	protected $childNodes = array();

	/**
	 * @var NodeInterface
	 */
	protected $node;

	/**
	 * @var array
	 */
	protected $stack = array();

	/**
	 * @param mixed $root NodeInterface, array (of nodes or expression parts) or a simple type that can be evaluated to boolean
	 */
	function __construct($input) {
		// First, evaluate everything that is not an ObjectAccessorNode, ArrayNode
		// or ViewHelperNode so we get all text, numbers, comparators and
		// groupers from the text parts of the expression. All other nodes
		// we leave intact for later processing
		if ($input instanceof RootNode) {
			$this->stack = $input->getChildNodes();
		} elseif (is_array($input)) {
			$this->stack = $input;
		} else {
			$this->stack = array(is_string($input) ? trim($input) : $input);
		}
	}

	/**
	 * @return array
	 */
	public function getStack() {
		return $this->stack;
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @return boolean the boolean value
	 */
	public function evaluate(RenderingContextInterface $renderingContext) {
		return self::evaluateStack($renderingContext, $this->stack);
	}

	/**
	 * @param NodeInterface $node
	 * @param RenderingContextInterface $renderingContext
	 * @return boolean
	 */
	public static function createFromNodeAndEvaluate(NodeInterface $node, RenderingContextInterface $renderingContext) {
		$booleanNode = new BooleanNode($node);
		return $booleanNode->evaluate($renderingContext);
	}

	/**
	 * Takes a stack of nodes evaluates it with the end result
	 * being a single boolean value. Creates new BooleanNodes
	 * recursively to process braced expressions as single units.
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @param array $expressionParts
	 * @return boolean the boolean value
	 */
	public static function evaluateStack(RenderingContextInterface $renderingContext, array $expressionParts) {
		$part = reset($expressionParts);
		$processedParts = array();
		do {
			if ($part instanceof TextNode || is_string($part)) {
				$text = $part instanceof TextNode ? $part->getText() : $part;
				$processedParts = array_merge($processedParts, self::splitExpression($text));
			} else {
				$processedParts[] = self::evaluateNodeIfNotAlreadyEvaluated($part, $renderingContext);
			}
		} while ($part = next($expressionParts));

		$expressionParts = &$processedParts;

		if (count($expressionParts) === 1) {
			$expression = $expressionParts[0];
			if (is_string($expression)) {
				$expression = ltrim($expression, '(');
				$expression = rtrim($expression, ')');
			}
			return self::convertToBoolean($expression);
		}
		if (in_array('(', $expressionParts, TRUE) && in_array(')', $expressionParts, TRUE)) {

			// We have a clearly defined grouping which means we can slice
			// the expression parts and evaluate our groupings recursively.
			$total = count($expressionParts);
			$parenthesisStart = array_search('(', $expressionParts, TRUE) + 1;
			$parenthesisEnd = $total - (array_search(')', array_reverse($expressionParts), TRUE) + 1);
			$subExpressions = array_slice(
				$expressionParts,
				$parenthesisStart,
				$total - $parenthesisEnd
			);
			$before = array_slice($expressionParts, 0, $parenthesisStart - 1);
			$after = $parenthesisEnd < count($expressionParts) ? array_slice($expressionParts, $parenthesisEnd + 1) : array();
			$expressionParts = array_merge($before, array(self::evaluateStack($renderingContext, $subExpressions)), $after);
		}

		// Now we dumb it down a bit and do not implement any special precedence
		// of the && and || groupers. Instead, we simply loop and consider each
		// part of the completely exploded (and recursively pre-evaluated) stack
		// of values, comparators and groupers to create one final verdict.

		// Loop variables are initially declared NULL for isset() to return FALSE.
		$left = $right = $verdict = $grouping = $comparator = NULL;

		foreach ($expressionParts as $index => $part) {

			// In this loop we consider if the $part is a grouping; if it is, we
			// store that as the grouping to be used when comparing the next part.
			// If the value is not a grouping we prepare to build one verdict that
			// is either a comparison or a simple boolean cast of one value.

			if (in_array($part, self::$groupers, TRUE)) {

				// The current part is a grouping instruction; evaluate what has
				// been collected so far.
				$newVerdict = $comparator ? self::evaluateComparator($comparator, $left, $right) : self::convertToBoolean($left);
				// We let the following method do what must be done based on whether
				// or not a grouping was provided and what that grouping is.
				$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, $newVerdict);
				// We reset the detected left, comparator AND grouping variables so
				// the next loop starts clean by collecting a left expression.
				$left = $comparator = $right = NULL;
				$grouping = $part;

			} elseif (in_array($part, self::$comparators, TRUE)) {

				$comparator = $part;

			} elseif (isset($left)) {

				// We have collected left side and comparator, now we collect the
				// right side. Next iteration or finalising below will then evaluate.
				$right = $part;

			} else {

				// Assign left part of our expression so the next iteration has it.
				$left = $part;

			}
		}

		if (isset($left) && isset($comparator) && isset($right)) {
			$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, self::evaluateComparator($comparator, $left, $right));
		} elseif (isset($left) && !isset($comparator) && !isset($right)) {
			$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, self::convertToBoolean($left));
		}

		return $verdict;
	}

	/**
	 * Evaluates what is potentially a NodeInterface instance. If the $node
	 * is such an instance, it is evaluated before being returned. The output
	 * value is therefore no longer a node regardless of the input, but can
	 * be a boolean, an array, an integer, objectaccessor etc.
	 *
	 * @param NodeInterface|mixed $node
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	protected static function evaluateNodeIfNotAlreadyEvaluated($node, RenderingContextInterface $renderingContext) {
		if ($node instanceof NodeInterface) {
			$node = $node->evaluate($renderingContext);
		}
		if (is_string($node)) {
			$node = self::trimQuotedString($node);
		}
		return $node;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function trimQuotedString($string) {
		$string = preg_match('/^[\'"].*[\'"]$/', $string) ? trim($string, $string{0}) : $string;
		if (is_numeric($string)) {
			// Rather than casting to integer or float we use PHP's type conversion via incrementing
			$string += 0;
		}
		return $string;
	}

	/**
	 * Makes a new verdict based on the old verdict, a new verdict and a grouping
	 * operator (&& or ||) which together determine the combined verdict. For
	 * example, $verdict=TRUE + $grouping='&&' + $newVerdict=FALSE = FALSE because
	 * (TRUE && FALSE) === FALSE.
	 * And * $verdict=TRUE + $grouping='||' + $newVerdict=FALSE = TRUE because
	 * (TRUE || FALSE) === TRUE.
	 *
	 * @param boolean $verdict
	 * @param string $grouping
	 * @param boolean $newVerdict
	 * @return boolean
	 */
	protected static function modifyVerdictBasedOnGrouping($verdict, $grouping, $newVerdict) {
		if (!is_bool($verdict) || empty($grouping)) {
			// There is no grouping or no existing verdict - we overrule the
			// verdict now to respect only the new verdict.
			return self::convertToBoolean($newVerdict);
		}
		// There is grouping - our new verdict MUST be created by taking
		// the existing verdict and evaluate the expression that consists
		// of our current evaluation, the comparator and the verdict from
		// the last comparison or casting.
		if ($grouping === '&&') {
			return (boolean) ($verdict && $newVerdict);
		} else {
			return (boolean) ($verdict || $newVerdict);
		}
	}

	/**
	 * Creates an array with all parts of the expression split by
	 * any whitespace that is not surrounded by single quotes.
	 *
	 * @param string $expression
	 * @return boolean
	 */
	protected static function splitExpression($expression) {
		$matches = array();
		preg_match_all('/\'[^\']+\'|\S+|\\)|\\(/', stripslashes($expression), $matches);
		return array_map(array(get_called_class(), 'trimQuotedString'), $matches[0]);
	}

	/**
	 * Do the actual comparison. Compares $leftSide and $rightSide with $comparator and emits a boolean value.
	 *
	 * Some special rules apply:
	 * - The == and != operators are comparing the Object Identity using === and !==, when one of the two
	 *   operands are objects.
	 * - For arithmetic comparisons (%, >, >=, <, <=), some special rules apply:
	 *   - arrays are only comparable with arrays, else the comparison yields FALSE
	 *   - objects are only comparable with objects, else the comparison yields FALSE
	 *   - the comparison is FALSE when two types are not comparable according to the table
	 *     "Comparison with various types" on http://php.net/manual/en/language.operators.comparison.php
	 *
	 * This function must be static public, as it is also directly called from cached templates.
	 *
	 * @param string $comparator
	 * @param mixed $left
	 * @param mixed $right
	 * @return boolean TRUE if comparison of left and right side using the comparator emit TRUE, false otherwise
	 * @throws Parser\Exception
	 */
	static public function evaluateComparator($comparator, $left, $right) {
		if ($comparator === '===') {
			return $left === $right;
		} elseif ($comparator === '==') {
			return (is_object($left) || is_object($right)) ? ($left === $right) : ($left == $right);
		} elseif ($comparator === '!=') {
			return (is_object($left) || is_object($right)) ? ($left !== $right) : ($left != $right);
		} elseif ($comparator === '%') {
			return (boolean) (self::isComparable($left, $right) ? ((integer) $left % (integer) $right) : FALSE);
		} elseif ($comparator === '>') {
			return self::isComparable($left, $right) ? ($left > $right) : FALSE;
		} elseif ($comparator === '>=') {
			return self::isComparable($left, $right) ? ($left >= $right) : FALSE;
		} elseif ($comparator === '<') {
			return self::isComparable($left, $right) ? ($left < $right) : FALSE;
		} elseif ($comparator === '<=') {
			return self::isComparable($left, $right) ? ($left <= $right) : FALSE;
		}
		throw new Parser\Exception('Comparator "' . $comparator . '" is not implemented.', 1244234398);
	}

	/**
	 * Checks whether two operands are comparable (based on their types). This implements
	 * the "Comparison with various types" table from http://php.net/manual/en/language.operators.comparison.php,
	 * only leaving out "array" with "anything" and "object" with anything; as we specify
	 * that arrays and objects are incomparable with anything else than their type.
	 *
	 * @param mixed $a
	 * @param mixed $b
	 * @return boolean TRUE if the operands can be compared using arithmetic operators, FALSE otherwise.
	 */
	static protected function isComparable($a, $b) {
		return (
			(is_null($a) || is_scalar($a) || is_resource($a)) && (is_null($b) || is_scalar($b) || is_resource($b)))
			|| (is_object($a) && is_object($b))
			|| (is_array($a) && is_array($b)
		);
	}

	/**
	 * Convert argument strings to their equivalents. Needed to handle strings with a boolean meaning.
	 *
	 * Must be public and static as it is used from inside cached templates.
	 *
	 * @param mixed $value Value to be converted to boolean
	 * @return boolean
	 */
	static public function convertToBoolean($value) {
		if (is_bool($value)) {
			return $value;
		}
		if (is_numeric($value)) {
			return (boolean) ((float) $value > 0);
		}
		if (is_string($value)) {
			$value = self::trimQuotedString($value);
			return (strtolower($value) !== 'false' && !empty($value));
		}
		if (is_array($value) || (is_object($value) && $value instanceof \Countable)) {
			return count($value) > 0;
		}
		if (is_object($value)) {
			return TRUE;
		}
		return FALSE;
	}

}
