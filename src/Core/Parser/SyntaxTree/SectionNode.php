<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Section Node - contains child nodes that make up
 * a Fluid section.
 */
class SectionNode extends AbstractNode
{
    protected $name;

    public function __construct(string $name, iterable $children)
    {
        $this->name = $name;
        $this->children = $children;
        $this->childNodes = $children;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $this->evaluateChildNodes($renderingContext);
    }
}