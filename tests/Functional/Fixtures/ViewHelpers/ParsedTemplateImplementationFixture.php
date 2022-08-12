<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

class ParsedTemplateImplementationFixture implements ParsedTemplateInterface
{
    public function setIdentifier($identifier)
    {
        // stub
    }

    public function getIdentifier()
    {
        return 'myIdentifier';
    }

    public function render(RenderingContextInterface $renderingContext)
    {
        return 'rendered by fixture';
    }

    public function getVariableContainer()
    {
        return new StandardVariableProvider();
    }

    public function getLayoutName(RenderingContextInterface $renderingContext)
    {
        // stub
    }

    public function addCompiledNamespaces(RenderingContextInterface $renderingContext)
    {
        // stub
    }

    public function hasLayout()
    {
        return false;
    }

    public function isCompilable()
    {
        return false;
    }

    public function isCompiled()
    {
        return false;
    }
}
