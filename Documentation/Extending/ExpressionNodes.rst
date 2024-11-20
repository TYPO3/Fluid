.. include:: /Includes.rst.txt

.. _creating-expressionnodes:

========================
Creating ExpressionNodes
========================

The :php:`ExpressionNode` concept is the most profound way you can manipulate the
Fluid language itself, adding to it new syntax options that can be used inside
the shorthand `{...}` syntax. Normally you are confined to using ViewHelpers
when you want such special processing in your templates - but using
ExpressionNodes allows you to add these processings as actual parts of the
templating language itself; avoiding the need to include a ViewHelper namespace.

Fluid itself provides the following types of `ExpressionNodes`:

1.  :php:`MathExpressionNode` which scans for and evaluates simple mathematical
    expressions like `{variable + 1}`.
2.  :php:`TernaryExpressionNode` which implements a ternary condition in Fluid syntax
    like `{ifsomething ? thenoutputthis : elsethis}`
3.  :php:`CastingExpressionNode` which casts variables to a certain type, e.g.
    `{suspectType as integer}`, `{myInteger as boolean}`.

An :php:`ExpressionNode` basically consists of one an expression matching pattern
(regex), one non-static method to evaluate the expression
:php:`public function evaluate(RenderingContextInterface $renderingContext)`
and a mirror of this function which can be called statically:
:php:`public static evaluteExpression(RenderingContextInterface $renderingContext, $expression)`.
The non-static method should then simply delegate to the static method and use
the expression stored in `$this->expression` as second parameter for the static
method call.

ExpressionNodes automatically support compilation and will generate compiled
code which stores the expression and calls the static :php:`evaluateExpression()`
method with the rendering context and the stored expression.

You should create your own ExpressionNodes if:

1.  You want a custom syntax in your Fluid templates (theoretical example:
    casting variables using `{(integer)variablename}`).
2.  You want to replace either of the above mentioned :php:`ExpressionNodes` with ones
    using the same, or an expanded version of their regular expression patterns
    to further extend the strings they capture and process.

.. _creating-expressionnodes-implementation:

Implementation
==============

An ExpressionNode is always one PHP class. Where you place it is
completely up to you - but to have the class actually be detected and used by
Fluid, it needs to be added to the rendering context by calling :php:`setExpressionNodeTypes()`.

In Fluid's default :php:`RenderingContext`, the following code is responsible for
returning expression node class names:

..  code-block:: php

    /**
     * List of class names implementing ExpressionNodeInterface
     * which will be consulted when an expression does not match
     * any built-in parser expression types.
     *
     * @var string
     */
    protected $expressionNodeTypes = [
        'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\CastingExpressionNode',
        'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\MathExpressionNode',
        'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\Expression\\TernaryExpressionNode',
    ];

    /**
     * @return string
     */
    public function getExpressionNodeTypes()
    {
        return $this->expressionNodeTypes;
    }

You may or may not want the listed expression nodes included, but if you change
the available expression types you should of course document this difference
about your implementation.

The following class is the math ExpressionNode from Fluid itself
which detects the `{a + 1}` and other simple mathematical operations.
To get this behavior, we need a (relatively
simple) regular expression and one method to evaluate the expression while being
aware of the rendering context (which stores all variables, controller name,
action name etc).

..  code-block:: php

    <?php
    namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression;

    use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

    class MathExpressionNode extends AbstractExpressionNode
    {
        /**
        * Pattern which detects the mathematical expressions with either
        * object accessor expressions or numbers on left and right hand
        * side of a mathematical operator inside curly braces, e.g.:
        *
        * {variable * 10}, {100 / variable}, {variable + variable2} etc.
        */
        public static string $detectionExpression = '/
            (
                {                                # Start of shorthand syntax
                    \s*                          # Allow whitespace before expression
                    (?:                          # Math expression is composed of...
                        [_a-zA-Z0-9\.]+(?:[\s]*[*+\^\/\%\-]{1}[\s]*[_a-zA-Z0-9\.]+)+   # Various math expressions left and right sides with any spaces
                        |(?R)                    # Other expressions inside
                    )+
                    \s*                          # Allow whitespace after expression
                }                                # End of shorthand syntax
            )/x';

        public static function evaluateExpression(RenderingContextInterface $renderingContext, string $expression, array $matches): int|float
        {
            // Split the expression on all recognized operators
            $matches = [];
            preg_match_all('/([+\-*\^\/\%]|[_a-zA-Z0-9\.]+)/s', $expression, $matches);
            $matches[0] = array_map('trim', $matches[0]);
            // Like the BooleanNode, we dumb down the processing logic to not apply
            // any special precedence on the priority of operators. We simply process
            // them in order.
            $result = array_shift($matches[0]);
            $result = static::getTemplateVariableOrValueItself($result, $renderingContext);
            $result = ($result == (int)$result) ? (int)$result : (float)$result;
            $operator = null;
            $operators = ['*', '^', '-', '+', '/', '%'];
            foreach ($matches[0] as $part) {
                if (in_array($part, $operators)) {
                    $operator = $part;
                } else {
                    $part = static::getTemplateVariableOrValueItself($part, $renderingContext);
                    $part = ($part == (int)$part) ? (int)$part : (float)$part;
                    $result = self::evaluateOperation($result, $operator, $part);
                }
            }
            return $result;
        }

        // ...
    }

Taking from this example class the following are the rules you must observe:

1.  You must either subclass the :php:`AbstractExpressionNode` class or implement the
    :php:`ExpressionNodeInterface` (subclassing the right class will automatically
    implement the right interface).
2.  You must provide the class with an exact property called
    :php:`public static $detectionExpression` which contains a string that is a perl
    regular expression which will result in at least one match when run against
    expressions you type in Fluid. It is vital that the property be both
    static and public and have the right name - it is accessed without
    instantiating the class.
3.  The class must have a :php:`public static function evaluateExpression` taking
    exactly the arguments above - nothing more, nothing less. The method must be
    able to work in a static context (it is called this way once templates have
    been compiled).
4.  The :php:`evaluateExpression` method may return any value type you desire, but
    like ViewHelpers, returning a non-string-compatible value implies that you
    should be careful about how you then use the expression; attempting to render
    a non-string-compatible value as a string may cause PHP warnings.
