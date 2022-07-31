<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ParsedTemplateImplementationFixture implements ParsedTemplateInterface
{
    public function setIdentifier($identifier)
    {
        // stub
    }

    public function getIdentifier()
    {
        // stub
    }

    public function render(RenderingContextInterface $renderingContext)
    {
        return 'rendered by fixture';
    }

    public function getVariableContainer()
    {
        // stub
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
        // stub
    }

    public function isCompilable()
    {
        // stub
    }

    public function isCompiled()
    {
        // stub
    }
}
