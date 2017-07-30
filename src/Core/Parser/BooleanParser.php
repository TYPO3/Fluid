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
 *  evaluate/compile: start the whole cycle
 *      parseOrToken: takes care of "||" parts
 *          evaluateOr: evaluate the "||" part if found
 *          parseAndToken: take care of "&&" parts
 *              evaluateAnd: evaluate "&&" part if found
 *              parseCompareToken: takes care any comparisons "==,!=,>,<,..."
 *                  evaluateCompare: evaluate the comparison if found
 *                  parseNotToken: takes care of any "!" negations
 *                      evaluateNot: evaluate the negation if found
 *                      parseBracketToken: takes care of any '()' parts and restarts the cycle
 *                          parseStringToken: takes care of any strings
 *                              evaluateTerm: evaluate terms from true/false/numeric/context
 *
 */
class BooleanParser
{

    /**
     * List of comparators to check in the parseCompareToken if the current
     * part of the expression is a comparator and needs to be compared
     */
    const COMPARATORS = '==,===,!==,!=,<=,>=,<,>,%';

    /**
     * Regex to parse a expression into tokens
     */
    const TOKENREGEX = '/
			\s*(
				\\\\\'
			|
				\\"
			|
				[\'"]
			|
				[A-Za-z0-9\.\{\}\-\\\\]+
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
			    [aA][nN][dD]
			|
				&&
			|
			    [oO][rR]
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
    protected $compileToCode = false;

    /**
     * Evaluate a expression to a boolean
     *
     * @param string $expression to be parsed
     * @param array $context containing variables that can be used in the expression
     * @return boolean
     */
    public function evaluate($expression, $context)
    {
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
    public function compile($expression)
    {
        $this->expression = $expression;
        $this->cursor = 0;
        $this->compileToCode = true;
        return $this->parseOrToken();
    }

    /**
     * The part of the expression we're currently focusing on based on the
     * tokenizing regex offset by the internally tracked cursor.
     *
     * @param boolean $includeWhitespace return surrounding whitespace with token
     * @return string
     */
    protected function peek($includeWhitespace = false)
    {
        preg_match(static::TOKENREGEX, substr($this->expression, $this->cursor), $matches);
        if ($includeWhitespace === true) {
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
    protected function consume($string)
    {
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
    protected function parseOrToken()
    {
        $x = $this->parseAndToken();
        while (($token = $this->peek()) && in_array(strtolower($token), ['||', 'or'])) {
            $this->consume($token);
            $y = $this->parseAndToken();

            if ($this->compileToCode === true) {
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
    protected function parseAndToken()
    {
        $x = $this->parseCompareToken();
        while (($token = $this->peek()) && in_array(strtolower($token), ['&&', 'and'])) {
            $this->consume($token);
            $y = $this->parseCompareToken();

            if ($this->compileToCode === true) {
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
    protected function parseCompareToken()
    {
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
    protected function parseNotToken()
    {
        if ($this->peek() === '!') {
            $this->consume('!');
            $x = $this->parseNotToken();

            if ($this->compileToCode === true) {
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
    protected function parseBracketToken()
    {
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
    protected function parseStringToken()
    {
        $t = $this->peek();
        if ($t === '\'' || $t === '"') {
            $stringIdentifier = $t;
            $string = $stringIdentifier;
            $this->consume($stringIdentifier);
            while (trim($t = $this->peek(true)) !== $stringIdentifier) {
                $this->consume($t);
                $string .= $t;
            }
            $this->consume($stringIdentifier);
            $string .= $stringIdentifier;
            if ($this->compileToCode === true) {
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
    protected function parseTermToken()
    {
        $t = $this->peek();
        $this->consume($t);
        return $this->evaluateTerm($t, $this->context);
    }

    /**
     * Evaluate an "and" comparison
     *
     * @param mixed $x
     * @param mixed $y
     * @return boolean
     */
    protected function evaluateAnd($x, $y)
    {
        return $x && $y;
    }

    /**
     * Evaluate an "or" comparison
     *
     * @param mixed $x
     * @param mixed $y
     * @return boolean
     */
    protected function evaluateOr($x, $y)
    {
        return $x || $y;
    }

    /**
     * Evaluate an "not" comparison
     *
     * @param mixed $x
     * @return boolean|string
     */
    protected function evaluateNot($x)
    {
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
    protected function evaluateCompare($x, $y, $comparator)
    {
        // enfore strong comparison for comparing two objects
        if ($comparator == '==' && is_object($x) && is_object($y)) {
            $comparator = '===';
        }
        if ($comparator == '!=' && is_object($x) && is_object($y)) {
            $comparator = '!==';
        }

        if ($this->compileToCode === true) {
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
    protected function evaluateTerm($x, $context)
    {
        if (isset($context[$x]) || (strpos($x, '{') === 0 && substr($x, -1) === '}')) {
            if ($this->compileToCode === true) {
                return '($context["' . trim($x, '{}') . '"])';
            }
            return $context[trim($x, '{}')];
        }

        if (is_numeric($x)) {
            if ($this->compileToCode === true) {
                return $x;
            }
            if (strpos($x, '.') !== false) {
                return (float)$x;
            } else {
                return (int)$x;
            }
        }

        if (trim(strtolower($x)) === 'true') {
            if ($this->compileToCode === true) {
                return 'TRUE';
            }
            return true;
        }
        if (trim(strtolower($x)) === 'false') {
            if ($this->compileToCode === true) {
                return 'FALSE';
            }
            return false;
        }

        if ($this->compileToCode === true) {
            return '"' . trim($x, '\'"') . '"';
        }

        return trim($x, '\'"');
    }
}
