<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Abstract node in the syntax tree which has been built.
 */
abstract class AbstractNode implements NodeInterface
{

    /**
     * List of Child Nodes.
     *
     * @var NodeInterface[]
     */
    protected $childNodes = [];

    /**
     * Evaluate all child nodes and return the evaluated results.
     *
     * @param RenderingContextInterface $renderingContext
     * @return mixed Normally, an object is returned - in case it is concatenated with a string, a string is returned.
     * @throws Parser\Exception
     */
    public function evaluateChildNodes(RenderingContextInterface $renderingContext)
    {
        $evaluatedNodes = [];
        foreach ($this->getChildNodes() as $childNode) {
            $evaluatedNodes[] = $this->evaluateChildNode($childNode, $renderingContext, false);
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
     * @param NodeInterface $node
     * @param RenderingContextInterface $renderingContext
     * @param boolean $cast
     * @return mixed
     */
    protected function evaluateChildNode(NodeInterface $node, RenderingContextInterface $renderingContext, $cast)
    {
        $output = $node->evaluate($renderingContext);
        if ($cast) {
            $output = $this->castToString($output);
        }
        return $output;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function castToString($value)
    {
        if (is_object($value) && !method_exists($value, '__toString')) {
            throw new Parser\Exception('Cannot cast object of type "' . get_class($value) . '" to string.', 1273753083);
        }
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $output = (string) $value;
        return $output;
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
     * @return RootNode|string|int|float|null
     */
    public function flatten(bool $extractNode = false)
    {
        if (empty($this->childNodes) && $extractNode) {
            return null;
        }
        $nodesCounted = count($this->childNodes);
        if ($nodesCounted === 1) {
            if ($extractNode) {
                if ($this->childNodes[0] instanceof TextNode) {
                    $text = $this->childNodes[0]->getText();
                    return is_numeric($text) ? $text + 0 : $text;
                }
            }
            return $this->childNodes[0];
        }
        /*
        if (!$containsNonTextNonNumericNodes) {
            $value = array_reduce($this->childNodes, function($initial, NodeInterface $node) {
                if ($node instanceof TextNode) {
                    return $initial . $node->getText();
                }
                if ($node instanceof NumericNode) {
                    return $initial . (string) $node->getValue();
                }
            }, '');
            if ($extractNode) {
                return $value;
            }
            return new TextNode($value);
        }
        */
        return $this;
    }

    /**
     * Returns all child nodes for a given node.
     * This is especially needed to implement the boolean expression language.
     *
     * @return NodeInterface[] A list of nodes
     */
    public function getChildNodes()
    {
        return $this->childNodes;
    }

    /**
     * Appends a sub node to this node. Is used inside the parser to append children
     *
     * @param NodeInterface $childNode The sub node to add
     * @return self
     */
    public function addChildNode(NodeInterface $childNode)
    {
        if ($childNode instanceof TextNode && ($last = end($this->childNodes)) && $last instanceof TextNode) {
            $last->appendText($childNode->getText());
        } else {
            $this->childNodes[] = $childNode;
        }
        return $this;
    }
}
