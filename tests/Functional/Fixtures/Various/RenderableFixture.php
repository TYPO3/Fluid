<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RenderableFixture implements RenderableInterface
{
    public function getName()
    {
        return 'RenderableFixture';
    }

    public function setName($name)
    {
        // stub
    }

    public function setNode(NodeInterface $node)
    {
        // stub
    }

    public function getNode()
    {
        return new TextNode(sprintf('%s (%s)', static::class, 'RenderableFixture'));
    }

    public function render(RenderingContextInterface $renderingContext): string
    {
        return 'rendered by renderable';
    }
}
