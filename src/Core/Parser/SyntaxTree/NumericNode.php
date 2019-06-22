<?php
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Numeric Syntax Tree Node - is a container for numeric values.
 */
class NumericNode extends AbstractNode
{

    /**
     * Contents of the numeric node
     * @var number
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param string|number $value value to store in this numericNode
     * @throws Exception
     */
    public function __construct($value)
    {
        if (!is_numeric($value)) {
            throw new Exception('Numeric node requires an argument of type number, "' . gettype($value) . '" given.');
        }
        $this->value = $value + 0;
    }

    /**
     * Return the value associated to the syntax tree.
     *
     * @param RenderingContextInterface $renderingContext
     * @return float|integer the value stored in this node/subtree.
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->value;
    }

    /**
     * Getter for value
     *
     * @return float|integer The value of this node
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * NumericNode does not allow adding child nodes, so this will always throw an exception.
     *
     * @param NodeInterface $childNode The sub node to add
     * @throws Exception
     * @return void
     */
    public function addChildNode(NodeInterface $childNode): void
    {
        throw new Exception('Numeric nodes may not contain child nodes, tried to add "' . get_class($childNode) . '".');
    }
}
