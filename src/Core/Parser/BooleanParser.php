<?php
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * This BooleanParser helps to parse and evaluate boolean expressions.
 * it's basically a recursive decent parser that uses a tokenizing regex
 * to walk a given expression while evaluating each step along the way.
 *
 * For a basic recursive decent exampel check out:
 * http://stackoverflow.com/questions/2093138/what-is-the-algorithm-for-parsing-expressions-in-infix-notation
 *
 * Parsingtree:
 *
 *	evaluate/compile: start the whole cycle
 *		parseOrToken: takes care of "||" parts
 *			evaluateOr: evaluate the "||" part if found
 *			parseAndToken: take care of "&&" parts
 *				evaluateAnd: evaluate "&&" part if found
 *				parseCompareToken: takes care any comparisons "==,!=,>,<,..."
 *					evaluateCompare: evaluate the comparison if found
 *					parseNotToken: takes care of any "!" negations
 *						evaluateNot: evaluate the negation if found
 *						parseBracketToken: takes care of any '()' parts and restarts the cycle
 *							parseStringToken: takes care of any strings
 *								evaluateTerm: evaluate terms from true/false/numeric/context
 *
 */
class BooleanParser {

	/**
	 * List of comparators to check in the parseCompareToken if the current
	 * part of the expression is a comparator and needs to be compared
	 */
	CONST COMPARATORS = '==,===,!==,!=,<=,>=,<,>,%';

	/**
	 * Regex to parse a expression into tokens
	 */
	CONST TOKENREGEX = '/
			\s*(
				\\\\\'
			|
				\\"
			|
				[\'"]
			|
				[A-Za-z0-9\.\{\}\\\\]+
			|
				\=\=\=
			|
				\=\=
			|
				!\=\=
			|
				!\=
			|
				<\=
			|
				>\=
			|
				<
			|
				>
			|
				%
			|
				\|\|
			|
				&&
			|
				.?
			)\s*
	/xs';

	/**
	 * Cursor that contains a integer value pointing to the location inside the
	 * expression string that is used by the peek function to look for the part of
	 * the expression that needs to be focused on next. This cursor is changed
	 * by the consume method, by "consuming" part of the expression.
	 *
	 * @var integer
	 */
	protected $cursor = 0;

	/**
	 * Expression that is parsed through peek and consume methods
	 *
	 * @var string
	 */
	protected $expression;

	/**
	 * Context containing all variables that are references in the expression
	 *
	 * @var array
	 */
	protected $context;

	/**
	 * Switch to enable compiling
	 *
	 * @var boolean
	 */
	protected $compileToCode = FALSE;

	/**
	 * Evaluate a expression to a boolean
	 *
	 * @param string $expression to be parsed
	 * @param array $context containing variables that can be used in the expression
	 * @return boolean
	 */
	public function evaluate($expression, $context) {
		$this->context = $context;
		$this->expression = $expression;
		$this->cursor = 0;
		return $this->parseOrToken();
	}

	/**
	 * Parse and compile an expression into an php equivalent
	 *
	 * @param string $expression to be parsed
	 * @return string
	 */
	public function compile($expression) {
		$this->expression = $expression;
		$this->cursor = 0;
		$this->compileToCode = TRUE;
		return $this->parseOrToken();
	}

	/**
	 * The part of the expression we're currently focusing on based on the
	 * tokenizing regex offset by the internally tracked cursor.
	 *
	 * @param boolean $includeWhitespace return surrounding whitespace with token
	 * @return string
	 */
	public function peek($includeWhitespace = FALSE) {
		preg_match(static::TOKENREGEX, substr($this->expression, $this->cursor), $matches);
		if ($includeWhitespace === TRUE) {
			return $matches[0];
		}
		return $matches[1];
	}

	/**
	 * Consume part of the current expression by setting the internal cursor
	 * to the position of the string in the expression and it's length
	 *
	 * @param string $string
	 * @return void
	 */
	public function consume($string) {
		if (strlen($string) === 0) {
			return;
		}
		$this->cursor = strpos($this->expression, $string, $this->cursor) + strlen($string);
	}

	/**
	 * Passes the torch down to the next deeper parsing leve (and)
	 * and checks then if there's a "or" expression that needs to be handled
	 *
	 * @return mixed
	 */
	public function parseOrToken() {
		$x = $this->parseAndToken();
		while ($this->peek() === '||') {
			$this->consume('||');
			$y = $this->parseAndToken();

			if ($this->compileToCode === TRUE) {
				$x = '(' . $x . ' || ' . $y . ')';
				continue;
			}
			$x = $this->evaluateOr($x, $y);
		}
		return $x;
	}

	/**
	 * Passes the torch down to the next deeper parsing leve (compare)
	 * and checks then if there's a "and" expression that needs to be handled
	 *
	 * @return mixed
	 */
	public function parseAndToken() {
		$x = $this->parseCompareToken();
		while ($this->peek() === '&&') {
			$this->consume('&&');
			$y = $this->parseCompareToken();

			if ($this->compileToCode === TRUE) {
				$x = '(' . $x . ' && ' . $y . ')';
				continue;
			}
			$x = $this->evaluateAnd($x, $y);
		}
		return $x;
	}

	/**
	 * Passes the torch down to the next deeper parsing leven (not)
	 * and checks then if there's a "compare" expression that needs to be handled
	 *
	 * @return mixed
	 */
	public function parseCompareToken() {
		$x = $this->parseNotToken();
		while (in_array($comparator = $this->peek(), explode(',', static::COMPARATORS))) {
			$this->consume($comparator);
			$y = $this->parseNotToken();
			$x = $this->evaluateCompare($x, $y, $comparator);
		}
		return $x;
	}

	/**
	 * Check if we have encountered an not expression or pass the torch down
	 * to the simpleToken method.
	 *
	 * @return mixed
	 */
	public function parseNotToken() {
		if ($this->peek() === '!') {
			$this->consume('!');
			$x = $this->parseNotToken();

			if ($this->compileToCode === TRUE) {
				return '!(' . $x . ')';
			}
			return $this->evaluateNot($x);
		}

		return $this->parseBracketToken();
	}

	/**
	 * Takes care of restarting the whole parsing loop if it encounters a "(" or ")"
	 * token or pass the torch down to the parseStringToken method
	 *
	 * @return mixed
	 */
	public function parseBracketToken() {
		$t = $this->peek();
		if ($t === '(') {
			$this->consume('(');
			$result = $this->parseOrToken();
			$this->consume(')');
			return $result;
		}

		return $this->parseStringToken();
	}

	/**
	 * Takes care of consuming pure string including whitespace or passes the torch
	 * down to the parseTermToken method
	 *
	 * @return mixed
	 */
	public function parseStringToken() {
		$t = $this->peek();
		if ($t === '\'' || $t === '"') {
			$stringIdentifier = $t;
			$string = $stringIdentifier;
			$this->consume($stringIdentifier);
			while (trim($t = $this->peek(TRUE)) !== $stringIdentifier) {
				$this->consume($t);
				$string .= $t;
			}
			$this->consume($stringIdentifier);
			$string .= $stringIdentifier;
			if ($this->compileToCode === TRUE) {
				return $string;
			}
			return $this->evaluateTerm($string, $this->context);
		}

		return $this->parseTermToken();
	}

	/**
	 * Takes care of restarting the whole parsing loop if it encounters a "(" or ")"
	 * token, consumes a pure string including whitespace or passes the torch
	 * down to the evaluateTerm method
	 *
	 * @return mixed
	 */
	public function parseTermToken() {
		$t = $this->peek();
		if ($this->isTerm($t)) {
			$this->consume($t);
			return $this->evaluateTerm($t, $this->context);
		}
		throw \RuntimeException('end of line, you should never have reached this point.');
	}

	/**
	 * Checks if the given string is a term or keyword
	 *
	 * @param string $x
	 */
	public function isTerm($x) {
		if (in_array($x, array('&&', '||', '!', '==', '\''))) {
			return FALSE;
		}

		return is_string($x);
	}

	/**
	 * Evaluate an "and" comparison
	 *
	 * @param mixed $x
	 * @param mixed $y
	 * @return boolean
	 */
	public function evaluateAnd($x, $y) {
		return $x && $y;
	}

	/**
	 * Evaluate an "or" comparison
	 *
	 * @param mixed $x
	 * @param mixed $y
	 * @return boolean
	 */
	public function evaluateOr($x, $y) {
		return $x || $y;
	}

	/**
	 * Evaluate an "not" comparison
	 *
	 * @param mixed $x
	 * @return boolean|string
	 */
	public function evaluateNot($x) {
		if ($this->compileToCode === TRUE) {
			return '!(' . $x . ')';
		}
		return !$x;
	}

	/**
	 * Compare two variables based on a specified comparator
	 *
	 * @param mixed $x
	 * @param mixed $y
	 * @param string $comparator
	 * @return boolean|string
	 */
	public function evaluateCompare($x, $y, $comparator) {
		// enfore strong comparison for comparing two objects
		if ($comparator == '==' && is_object($x) && is_object($y)) {
			$comparator = '===';
		}
		if ($comparator == '!=' && is_object($x) && is_object($y)) {
			$comparator = '!==';
		}

		if ($this->compileToCode === TRUE) {
			return sprintf('(%s %s %s)', $x, $comparator, $y);
		}

		switch ($comparator) {
			case '==':
				$x = ($x == $y);
				break;

			case '===':
				$x = ($x === $y);
				break;

			case '!=':
				$x = ($x != $y);
				break;

			case '!==':
				$x = ($x !== $y);
				break;

			case '<=':
				$x = ($x <= $y);
				break;

			case '>=':
				$x = ($x >= $y);
				break;

			case '<':
				$x = ($x < $y);
				break;

			case '>':
				$x = ($x > $y);
				break;

			case '%':
				$x = ($x % $y);
				break;
		}
		return $x;
	}

	/**
	 * Takes care of fetching terms from the context, converting to float/int,
	 * converting true/false keywords into boolean or trim the final string of
	 * quotation marks
	 *
	 * @param string $x
	 * @param array $context
	 * @return mixed
	 */
	public function evaluateTerm($x, $context) {
		if (substr($x, 0, 1) === '{' && substr($x, -1) === '}') {
			if ($this->compileToCode === TRUE) {
				return '($context["' . trim($x, '{}') . '"])';
			}
			return $context[trim($x, '{}')];
		}

		if (is_numeric($x)) {
			if ($this->compileToCode === TRUE) {
				return $x;
			}
			if (stristr($x, '.')) {
				return floatval($x);
			} else {
				return intval($x);
			}
		}

		if (trim(strtolower($x)) === 'true') {
			if ($this->compileToCode === TRUE) {
				return 'TRUE';
			}
			return TRUE;
		}
		if (trim(strtolower($x)) === 'false') {
			if ($this->compileToCode === TRUE) {
				return 'FALSE';
			}
			return FALSE;
		}

		if (isset($context[$x]) === TRUE) {
			if ($this->compileToCode === TRUE) {
				return '($context["' . $x . '"])';
			}
			return $context[$x];
		}

		if ($this->compileToCode === TRUE) {
			return '"' . trim($x, '\'"') . '"';
		}

		return trim($x, '\'"');
	}
}
