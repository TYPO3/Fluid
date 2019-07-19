<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollectionInterface;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Abstract node in the syntax tree which has been built.
 */
abstract class AbstractNode extends AbstractComponent
{

    /**
     * List of Child Nodes.
     *
     * @var ComponentInterface[]
     */
    protected $childNodes = [];

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollectionInterface $arguments = null)
    {
        return $this->evaluate($renderingContext);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->evaluateChildNodes($renderingContext);
    }

    /**
     * Evaluate all child nodes and return the evaluated results.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Normally, an object is returned - in case it is concatenated with a string, a string is returned.
     * @throws Exception
     */
    protected function evaluateChildNodes(RenderingContextInterface $renderingContext)
    {
        $evaluatedNodes = [];
        foreach ($this->getChildren() as $childNode) {
            $evaluatedNodes[] = $childNode->execute($renderingContext);
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
            parent::addChild($component);
        }
        return $this;
    }
}
