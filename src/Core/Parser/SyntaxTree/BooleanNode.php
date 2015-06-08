<?php
namespace NamelessCoder\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Parser;
use NamelessCoder\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which is used inside boolean arguments
 */
class BooleanNode extends AbstractNode {

	/**
	 * List of comparators which are supported in the boolean expression language.
	 *
	 * Make sure that if one string is contained in one another, the longer
	 * string is listed BEFORE the shorter one.
	 * Example: put ">=" before ">"
	 *
	 * @var array
	 */
	static protected $comparators = array('==', '!=', '%', '>=', '>', '<=', '<');

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
			|==|!=|%|>=|>|<=|<  # We allow all comparators
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
	 * @param NodeInterface $root
	 */
	function __construct(NodeInterface $root) {
		// First, evaluate everything that is not an ObjectAccessorNode, ArrayNode
		// or ViewHelperNode so we get all text, numbers, comparators and
		// groupers from the text parts of the expression. All other nodes
		// we leave intact for later processing
		if ($root instanceof TextNode) {
			$this->stack = array($root);
		} elseif ($root instanceof RootNode) {
			$this->stack = $root->getChildNodes();
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

		// ---------------- BEGIN STRUCTURING ---------------/

		// Second, reciprocate through nodes to create new BooleanNodes from the
		// inside out. Loop from back to front and repeat until the contents of
		// all braces have been extracted into BooleanNodes:
		$i = count($expressionParts);

		while ($i > 0 && $i-- && ($node = $expressionParts[$i])) {
			if (!$node instanceof TextNode) {
				continue;
			}
			$expressionParts[$i] = $node->evaluate($renderingContext);
			$node = $expressionParts[$i];
			$node = trim($node);
			if (strpos($node, '(') === FALSE) {
				// Node is a plain, non-braced expression that we can evaluate.
				$expressionParts = self::mergeArraysAtIndex($i, $expressionParts, self::splitExpression($node));
				$i = count($expressionParts);
				continue;
			}

			// We've found an opening brace and will now split the TextNode
			// into two, replace the old expression part with the first part
			// of the now separated text, create a new TextNode from the
			// remaining text and start checking for a closing brace:
			$parts = explode('(', $node, 2);
			$parts = array_map('trim', $parts);
			$newNode = new RootNode();

			// Now, if $parts[1] also contains at least one closing brace,
			// we split that as well and will then have our complete new
			// child nodes for the expression. We only care about $parts[1]
			// because caring about $node might yield a false positive when
			// there is a closing brace before the opening brace.
			$cut = strpos($parts[1], ')');
			if ($cut !== FALSE) {
				if (strlen($parts[0])) {
					$newNode->addChildNode(new TextNode($parts[0]));
				}
				if ($cut === strlen($parts[1]) - 1) {
					$newNode->addChildNode(new TextNode(substr($parts[1], 0, -1)));
				} else {
					$newNode->addChildNode(new TextNode(substr($parts[1], 0, $cut)));
					$newNode->addChildNode(new TextNode(substr($parts[1], $cut + 1)));
				}
				$booleanNode = new BooleanNode($newNode);
				$expressionParts = self::mergeArraysAtIndex(
					$i, $expressionParts, self::createFromNodeAndEvaluate($newNode, $renderingContext)
				);
				// Then we *RESET* the iteration counter to the *NEW MAX* so
				// the next iteration will begin this loop all over, thus
				// reciprocating back/front multiple times until all brace
				// groupings are gone, each replaced by simple TRUE/FALSE.
				$i = count($expressionParts);
			}
		}

		// ---------------- END STRUCTURING, BEGIN EVALUATION ---------------/

		// Now we dumb it down a bit and do not implement any special precedence
		// of the && and || groupers. Instead, we simply loop and consider each
		// part of the completely exploded (and recursively pre-evaluated) stack
		// of values, comparators and groupers to create one final verdict. Start
		// by picking off the first (and possibly only) element and turning that
		// into a boolean value to use as the beginning verdict.

		// First item is always left side.
		$left = self::evaluateNodeIfNotAlreadyEvaluated(array_shift($expressionParts), $renderingContext);
		// Initial verdict is always left side of expression.
		$verdict = self::convertToBoolean($left);
		// Grouping and comparator must always be NULL initially.
		$grouping = $comparator = NULL;

		foreach ($expressionParts as $index => $part) {

			$part = self::evaluateNodeIfNotAlreadyEvaluated($part, $renderingContext);

			// In this loop we consider if the $part is a grouping; if it is, we
			// store that as the grouping to be used when comparing the next part.
			// If the value is not a grouping we prepare to build one verdict that
			// is either a comparison or a simple boolean cast of one value.
			if (in_array($part, self::$groupers)) {

				$grouping = $part;

			} elseif ($left && $comparator) {

				// The condition cases below have through iteration built up the
				// necessary parts for a comparison. The current part will be the
				// right hand side of the expression - and we have enough to evaluate.
				$newVerdict = self::evaluateComparator($comparator, $left, $part);
				// We let the following method do what must be done based on whether
				// or not a grouping was provided and what that grouping is.
				$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, $newVerdict);
				// We reset the detected left, comparator AND grouping variables so
				// the next loop starts clean by collecting a left expression.
				$left = $comparator = $grouping = NULL;

			} elseif (!isset($expressionParts[$index + 1]) || in_array($expressionParts[$index + 1], self::$groupers)) {

				// We've looked ahead and the next value either does not exist or is
				// a grouping operator. In either case we must now evaluate our
				// expression. We know that the expression is only a single value
				// currently stored in the left hand side of the expression.
				$newVerdict = $comparator ? self::evaluateComparator($comparator, $left, $part) : self::convertToBoolean($part);
				$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, $newVerdict);
				// We reset the left side of the expression but no other values.
				$left = NULL;

			} elseif (isset($expressionParts[$index + 1]) && in_array($expressionParts[$index + 1], self::$comparators)) {

				// We've looked ahead and the next value exists and is a comparator.
				// At this point we must modify our verdict either to create the
				// final value or to prepare for a coming grouping.
				$verdict = self::modifyVerdictBasedOnGrouping($verdict, $grouping, self::convertToBoolean($left));

			} elseif (in_array($part, self::$comparators)) {
				// The current part is a comparator. We store the comparator now
				// and the next iteration will contain the right side of the
				// expression (which will be matched by the first case of this "if".
				$comparator = $part;

			}
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
		return $node;
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
		if (empty($grouping)) {
			// There is no grouping - we change the verdict now because our
			// previous verdict was a boolean cast of the left side and the
			// new verdict is a proper comparison as indended.
			return (boolean) $newVerdict;
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
	 * Merges $array2 into $array1 by splitting $array1 at $index
	 * and merging the results in order $before + $array2 + $after.
	 * The $array2 variable supports mixed; if the variable is not
	 * an array, a single-item array is created from it.
	 *
	 * @param integer $index
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected static function mergeArraysAtIndex($index, array $array1, $array2) {
		if (!is_array($array2)) {
			$array2 = array($array2);
		}
		$before = array_slice($array1, 0, $index);
		$after = array_slice($array1, $index + 1);
		return array_merge($before, $array2, $after);
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
		preg_match_all('/\'[^\']+\'|\S+/', $expression, $matches);
		$matches[0] = array_map('trim', $matches[0]);
		return $matches[0];
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
		if (is_string($left)) {
			$left = trim($left, "\t\n\r\0\x0B'\"");
		}
		if (is_string($right)) {
			$right = trim($right, "\t\n\r\0\x0B'\"");
		}
		if ($comparator === '==') {
			return (boolean) ((is_object($left) && is_object($right)) ? ($left === $right) : ($left == $right));
		} elseif ($comparator === '!=') {
			return (boolean) ((is_object($left) && is_object($right)) ? ($left !== $right) : ($left != $right));
		} elseif ($comparator === '%') {
			return (boolean) (self::isComparable($left, $right) ? ((integer) $left % (integer) $right) : FALSE);
		} elseif ($comparator === '>') {
			return (boolean) (self::isComparable($left, $right) ? ($left > $right) : FALSE);
		} elseif ($comparator === '>=') {
			return (boolean) (self::isComparable($left, $right) ? ($left >= $right) : FALSE);
		} elseif ($comparator === '<') {
			return (boolean) (self::isComparable($left, $right) ? ($left < $right) : FALSE);
		} elseif ($comparator === '<=') {
			return (boolean) (self::isComparable($left, $right) ? ($left <= $right) : FALSE);
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
		$stringA = is_string($a);
		$stringB = is_string($b);
		return (
			(is_null($a) || $stringA) && $stringB)
			|| (($stringA || is_resource($a) || is_numeric($a)) && ($stringB || is_resource($b) || is_numeric($b)))
			|| (is_bool($a) || is_null($a))
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
			return $value > 0;
		}
		if (is_string($value)) {
			$value = trim($value, "\t\n\r\0\x0B'\"");
			return (!empty($value) && strtolower($value) !== 'false');
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
