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
     * Evaluate a expression to a boolean
     *
     * @param string $expression to be parsed
     * @param array $context containing variables that can be used in the expression
     * @return mixed
     */
    public function evaluate(string $expression, array $context): mixed
    {
        $this->context = $context;
        $this->expression = $expression;
        $this->cursor = 0;
        return (function ($context): mixed {
            $code = 'return ' . $this->parseOrToken() . ';';
            return eval($code);
        })($context);
    }

    /**
     * Parse and compile an expression into an php equivalent
     *
     * @param string $expression to be parsed
     */
    public function compile(string $expression): string
    {
        $this->context = [];
        $this->expression = $expression;
        $this->cursor = 0;
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
     */
    protected function parseOrToken(): string
    {
        $x = $this->parseAndToken();
        while (($token = $this->peek()) && in_array(strtolower($token), ['||', 'or'])) {
            $this->consume($token);
            $y = $this->parseAndToken();
            $x = '(' . $x . ' || ' . $y . ')';
        }
        return $x;
    }

    /**
     * Passes the torch down to the next deeper parsing leve (compare)
     * and checks then if there's a "and" expression that needs to be handled
     *
     * @return mixed
     */
    protected function parseAndToken(): string
    {
        $x = $this->parseCompareToken();
        while (($token = $this->peek()) && in_array(strtolower($token), ['&&', 'and'])) {
            $this->consume($token);
            $y = $this->parseCompareToken();
            $x = '(' . $x . ' && ' . $y . ')';
        }
        return $x;
    }

    /**
     * Passes the torch down to the next deeper parsing leven (not)
     * and checks then if there's a "compare" expression that needs to be handled
     *
     * @return mixed
     */
    protected function parseCompareToken(): string
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
    protected function parseNotToken(): string
    {
        if ($this->peek() === '!') {
            $this->consume('!');
            $x = $this->parseNotToken();

            return '!(' . $x . ')';
        }

        return $this->parseBracketToken();
    }

    /**
     * Takes care of restarting the whole parsing loop if it encounters a "(" or ")"
     * token or pass the torch down to the parseStringToken method
     *
     * @return mixed
     */
    protected function parseBracketToken(): string
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
     */
    protected function parseStringToken(): string
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
            return $string;
        }

        return $this->parseTermToken();
    }

    /**
     * Takes care of restarting the whole parsing loop if it encounters a "(" or ")"
     * token, consumes a pure string including whitespace or passes the torch
     * down to the evaluateTerm method
     */
    protected function parseTermToken(): string
    {
        $t = $this->peek();
        $this->consume($t);
        return $this->evaluateTerm($t);
    }

    /**
     * Compare two variables based on a specified comparator
     */
    protected function evaluateCompare(mixed $x, mixed $y, string $comparator): string
    {
        // enfore strong comparison for comparing two objects
        if ($comparator === '==' && is_object($x) && is_object($y)) {
            $comparator = '===';
        }
        if ($comparator === '!=' && is_object($x) && is_object($y)) {
            $comparator = '!==';
        }

        return sprintf('(%s %s %s)', $x, $comparator, $y);
    }

    /**
     * Takes care of fetching terms from the context, converting to float/int,
     * converting true/false keywords into boolean or trim the final string of
     * quotation marks
     */
    protected function evaluateTerm(string $x): string
    {
        if (isset($this->context[$x]) || (mb_strpos($x, '{') === 0 && mb_substr($x, -1) === '}')) {
            return BooleanParser::class . '::convertNodeToBoolean($context["' . trim($x, '{}') . '"])';
        }

        if (is_numeric($x)) {
            return (string)$x;
        }

        if (trim(strtolower($x)) === 'true') {
            return 'TRUE';
        }
        if (trim(strtolower($x)) === 'false') {
            return 'FALSE';
        }

        return '"' . trim($x, '\'"') . '"';
    }

    public static function convertNodeToBoolean(mixed $value)
    {
        if ($value instanceof \Countable) {
            return count($value) > 0;
        }
        return $value;
    }
}
