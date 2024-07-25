<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Numeric Syntax Tree Node - is a container for numeric values.
 *
 * @internal
 * @todo Make class final.
 */
class NumericNode extends AbstractNode
{
    /**
     * Contents of the numeric node
     * @var number
     */
    protected float|int $value;

    /**
     * Constructor.
     *
     * @param float|int|string $value value to store in this numericNode
     * @throws Parser\Exception
     */
    public function __construct(float|int|string $value)
    {
        if (!is_numeric($value)) {
            throw new Parser\Exception('Numeric node requires an argument of type number, "' . gettype($value) . '" given.');
        }
        $this->value = $value + 0;
    }

    /**
     * Return the value associated to the syntax tree.
     *
     * @return float|int the value stored in this node/subtree.
     */
    public function evaluate(RenderingContextInterface $renderingContext): float|int
    {
        return $this->value;
    }

    public function getValue(): float|int
    {
        return $this->value;
    }

    /**
     * NumericNode does not allow adding child nodes, so this will always throw an exception.
     *
     * @param NodeInterface $childNode The sub node to add
     * @throws Parser\Exception
     */
    public function addChildNode(NodeInterface $childNode): void
    {
        throw new Parser\Exception('Numeric nodes may not contain child nodes, tried to add "' . get_class($childNode) . '".');
    }

    public function convert(TemplateCompiler $templateCompiler): array
    {
        return [
            'initialization' => '',
            'execution' => $this->value,
        ];
    }
}
