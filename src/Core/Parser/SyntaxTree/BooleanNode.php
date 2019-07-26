<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\BooleanParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * A node which is used inside boolean arguments
 */
class BooleanNode extends AbstractComponent
{

    /**
     * Stack of expression nodes to be evaluated
     *
     * @var ComponentInterface[]
     */
    protected $childNodes = [];

    /**
     * @var ComponentInterface
     */
    protected $node;

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * @param mixed $input NodeInterface, array (of nodes or expression parts) or a simple type that can be evaluated to boolean
     */
    public function __construct($input)
    {
        // First, evaluate everything that is not an ObjectAccessorNode, ArrayNode
        // or ViewHelperNode so we get all text, numbers, comparators and
        // groupers from the text parts of the expression. All other nodes
        // we leave intact for later processing
        if ($input instanceof RootNode) {
            $this->stack = $input->getChildren();
        } elseif (is_array($input)) {
            $this->stack = $input;
        } else {
            $this->stack = [is_string($input) ? trim($input) : $input];
        }
    }

    /**
     * @return array
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    public function execute(RenderingContextInterface $renderingContext): bool
    {
        return self::evaluateStack($renderingContext, $this->stack);
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
    protected static function evaluateStack(RenderingContextInterface $renderingContext, array $expressionParts): bool
    {
        $expression = static::reconcatenateExpression($expressionParts);
        $context = static::gatherContext($renderingContext, $expressionParts);

        $parser = new BooleanParser();
        return static::convertToBoolean($parser->evaluate($expression, $context), $renderingContext);
    }

    /**
     * Walk all expressionParts and concatenate an expression string
     *
     * @param array $expressionParts
     * @return string
     */
    protected static function reconcatenateExpression(array $expressionParts): string
    {
        $merged = [];
        foreach ($expressionParts as $key => $expressionPart) {
            if ($expressionPart instanceof TextNode || is_string($expressionPart)) {
                $merged[] = $expressionPart instanceof TextNode ? $expressionPart->getText() : $expressionPart;
            } elseif ($expressionPart instanceof ComponentInterface) {
                $merged[] = '{node' . $key . '}';
            } else {
                $merged[] = '{node' . $key . '}';
            }
        }
        return implode('', $merged);
    }

    /**
     * Walk all expressionParts and gather a context array of all non textNode parts
     *
     * @param RenderingContextInterface $renderingContext
     * @param array $expressionParts
     * @return array
     */
    protected static function gatherContext(RenderingContextInterface $renderingContext, array $expressionParts): array
    {
        $context = [];
        foreach ($expressionParts as $key => $expressionPart) {
            if ($expressionPart instanceof ComponentInterface) {
                $context['node' . $key] = $expressionPart->execute($renderingContext);
            } else {
                $context['node' . $key] = $expressionPart;
            }
        }
        return $context;
    }

    /**
     * Convert argument strings to their equivalents. Needed to handle strings with a boolean meaning.
     *
     * Must be public and static as it is used from inside cached templates.
     *
     * @param mixed $value Value to be converted to boolean
     * @param RenderingContextInterface $renderingContext
     * @return boolean
     */
    public static function convertToBoolean($value, RenderingContextInterface $renderingContext): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (boolean) ((float) $value);
        }
        if (is_string($value)) {
            if (strlen($value) === 0) {
                return false;
            }
            $value = $renderingContext->getTemplateParser()->unquoteString($value);
            return (strtolower($value) !== 'false' && !empty($value));
        }
        if (is_array($value) || (is_object($value) && $value instanceof \Countable)) {
            return count($value) > 0;
        }
        return is_object($value);
    }
}
