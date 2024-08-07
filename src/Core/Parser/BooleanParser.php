<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

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
 */
class BooleanParser
{
    /**
     * List of comparators to check in the parseCompareToken if the current
     * part of the expression is a comparator and needs to be compared
     */
    public const COMPARATORS = '==,===,!==,!=,<=,>=,<,>,%';

    /**
     * Regex to parse a expression into tokens
     */
    public const TOKENREGEX = '/
			\s*(
				\\\\\'
			|
				\\"
			|
				[\'"]
			|
				[_A-Za-z0-9\.\{\}\-\\\\]+
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
	/xsu';

    /**
     * Cursor that contains a integer value pointing to the location inside the
     * expression string that is used by the peek function to look for the part of
     * the expression that needs to be focused on next. This cursor is changed
     * by the consume method, by "consuming" part of the expression.
     */
    protected int $cursor = 0;

    /**
     * Expression that is parsed through peek and consume methods
     */
    protected string $expression;

    /**
     * Context containing all variables that are references in the expression
     */
    protected array $context = [];

    /**
     * Switch to enable compiling
     */
    protected bool $compileToCode = false;

    /**
     * Evaluate a expression to a boolean
     *
     * @param string $expression to be parsed
     * @param array $context containing variables that can be used in the expression
     * @return mixed
     *
     * @todo further investigate return types of all evaluate* and parse* methods. Expected
     *       return type would be something like bool|string, but in fact these methods return
     *       all kinds of types, which are probably converted to bool anyways in the end.
     *       Also, the global switch of $compileToCode seems very dirty. If that is really
     *       necessary, there should be two separate implementations: One that generates code
     *       for cache storage (using string as return type) and one for immediate execution
     *       (using bool).
     */
    public function evaluate(string $expression, array $context)
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
     */
    public function compile(string $expression): string
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
     * @param bool $includeWhitespace return surrounding whitespace with token
     */
    protected function peek(bool $includeWhitespace = false): string
    {
        preg_match(static::TOKENREGEX, mb_substr($this->expression, $this->cursor), $matches);
        if ($includeWhitespace === true) {
            return $matches[0];
        }
        return $matches[1];
    }

    /**
     * Consume part of the current expression by setting the internal cursor
     * to the position of the string in the expression and it's length
     */
    protected function consume(string $string): void
    {
        if (mb_strlen($string) === 0) {
            return;
        }
        $this->cursor = mb_strpos($this->expression, $string, $this->cursor) + mb_strlen($string);
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

                if ($t === '') {
                    throw new Exception(sprintf('Closing string token expected in boolean expression "%s".', $this->expression), 1697479462);
                }
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
     */
    protected function evaluateAnd(mixed $x, mixed $y): bool
    {
        return $x && $y;
    }

    /**
     * Evaluate an "or" comparison
     */
    protected function evaluateOr(mixed $x, mixed $y): bool
    {
        return $x || $y;
    }

    /**
     * Evaluate an "not" comparison
     */
    protected function evaluateNot(mixed $x): bool
    {
        return !$x;
    }

    /**
     * Compare two variables based on a specified comparator
     *
     * @return mixed returns string if $compileToCode is set to true
     */
    protected function evaluateCompare(mixed $x, mixed $y, string $comparator)
    {
        // enfore strong comparison for comparing two objects
        if ($comparator === '==' && is_object($x) && is_object($y)) {
            $comparator = '===';
        }
        if ($comparator === '!=' && is_object($x) && is_object($y)) {
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
     * @return mixed
     */
    protected function evaluateTerm(string $x, array $context)
    {
        if (isset($context[$x]) || (mb_strpos($x, '{') === 0 && mb_substr($x, -1) === '}')) {
            if ($this->compileToCode === true) {
                return BooleanParser::class . '::convertNodeToBoolean($context["' . trim($x, '{}') . '"])';
            }
            return self::convertNodeToBoolean($context[trim($x, '{}')]);
        }

        if (is_numeric($x)) {
            if ($this->compileToCode === true) {
                return $x;
            }
            if (mb_strpos($x, '.') !== false) {
                return (float)$x;
            }
            return (int)$x;
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

    public static function convertNodeToBoolean(mixed $value)
    {
        if ($value instanceof \Countable) {
            return count($value) > 0;
        }
        return $value;
    }
}
