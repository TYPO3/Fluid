<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Base Component Class
 *
 * Contains standard implementations for some of the more
 * universal methods a Component supports, e.g. handling
 * of child components and resolving of named children.
 */
abstract class AbstractComponent implements ComponentInterface
{
    /**
     * Unnamed children indexed by numeric position in array
     *
     * @var ComponentInterface[]
     */
    protected $children = [];

    /**
     * @var string|null
     */
    protected $name;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     * @see isChildrenEscapingEnabled()
     *
     * Note: If this is NULL the value of $this->escapingInterceptorEnabled is considered for backwards compatibility
     *
     * @var boolean|null
     * @api
     */
    protected $escapeChildren = null;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     * @see isOutputEscapingEnabled()
     *
     * @var boolean|null
     * @api
     */
    protected $escapeOutput = null;

    /**
     * @var ArgumentCollectionInterface|null
     */
    protected $parsedArguments = null;

    public function onOpen(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null): ComponentInterface
    {
        $this->parsedArguments = $this->parsedArguments ?? $arguments;
        return $this;
    }

    public function onClose(RenderingContextInterface $renderingContext): ComponentInterface
    {
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getArguments(): ArgumentCollectionInterface
    {
        return $this->parsedArguments ?? ($this->parsedArguments = new ArgumentCollection());
    }

    public function addChild(ComponentInterface $component): ComponentInterface
    {
        if ($component instanceof RootNode) {
            // Assimilate child nodes instead of allowing a root node inside a root node.
            foreach ($component->getChildren() as $node) {
                $this->addChild($node);
            }
        } elseif ($component instanceof TextNode && ($last = end($this->children)) && $last instanceof TextNode) {
            $last->appendText($component->getText());
        } else {
            $this->children[] = $component;
        }
        return $this;
    }

    public function getNamedChild(string $name): ComponentInterface
    {
        foreach ($this->children as $child) {
            if ($child->getName() === $name) {
                return $child;
            }
            try {
                return $child->getNamedChild($name);
            } catch (ChildNotFoundException $exception) {

            }
        }
        throw new ChildNotFoundException(sprintf('Child with name "%s" not found', $name), 1562757835);
    }

    /**
     * @return ComponentInterface[]
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * Returns one of the following:
     *
     * - Itself, if there is more than one child node and one or more nodes are not TextNode or NumericNode
     * - A plain value if there is a single child node of type TextNode or NumericNode
     * - The one child node if there is only a single child node not of type TextNode or NumericNode
     * - Null if there are no child nodes at all.
     *
     * @param bool $extractNode If TRUE, will extract the value of a single node if the node type contains a scalar value
     * @return ComponentInterface|string|int|float|null
     */
    public function flatten(bool $extractNode = false)
    {
        if (empty($this->children) && $extractNode) {
            return null;
        }
        $nodesCounted = count($this->children);
        if ($nodesCounted === 1) {
            if ($extractNode) {
                if ($this->children[0] instanceof TextNode) {
                    $text = $this->children[0]->getText();
                    return is_numeric($text) ? $text + 0 : $text;
                }
            }
            return $this->children[0];
        }
        return $this;
    }

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeChildren instead!
     *
     * @return boolean
     */
    public function isChildrenEscapingEnabled(): bool
    {
        if ($this->escapeChildren === null) {
            // Disable children escaping automatically, if output escaping is on anyway.
            return !$this->isOutputEscapingEnabled();
        }
        return $this->escapeChildren !== false;
    }

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeOutput instead!
     *
     * @return boolean
     */
    public function isOutputEscapingEnabled(): bool
    {
        return $this->escapeOutput !== false;
    }

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        return $this->evaluateChildren($renderingContext);
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        return true;
    }

    /**
     * Evaluate all child nodes and return the evaluated results.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Normally, an object is returned - in case it is concatenated with a string, a string is returned.
     * @throws Exception
     */
    protected function evaluateChildren(RenderingContextInterface $renderingContext)
    {
        $evaluatedNodes = [];
        foreach ($this->getChildren() as $childNode) {
            $evaluatedNodes[] = $childNode->execute($renderingContext, $childNode->getArguments());
        }
        // Make decisions about what to actually return
        if (empty($evaluatedNodes)) {
            return null;
        }
        if (count($evaluatedNodes) === 1) {
            return $evaluatedNodes[0];
        }
        return implode('', array_map([$this, 'castToString'], $evaluatedNodes));
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function castToString($value): string
    {
        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new Exception('Cannot cast object of type "' . get_class($value) . '" to string.', 1273753083);
        }
        return (string) $value;
    }
}