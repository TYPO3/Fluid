<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderableInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RenderableFixture implements RenderableInterface
{

    /**
     * @inheritDoc
     */
    public function getName()
    {
        // stub
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        // stub
    }

    /**
     * @inheritDoc
     */
    public function setNode(NodeInterface $node)
    {
        // stub
    }

    /**
     * @inheritDoc
     */
    public function getNode()
    {
        // stub
    }

    /**
     * @inheritDoc
     */
    public function render(RenderingContextInterface $renderingContext): string
    {
        return 'rendered by renderable';
    }
}
