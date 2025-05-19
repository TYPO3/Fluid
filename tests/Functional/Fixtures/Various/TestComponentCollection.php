<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplateAwareViewInterface;
use TYPO3Fluid\Fluid\View\TemplateView;

final class TestComponentCollection extends AbstractComponentCollection
{
    public function createView(RenderingContextInterface $renderingContext): TemplateAwareViewInterface
    {
        $renderingContext->getTemplatePaths()->setTemplateRootPaths([
            __DIR__ . '/../Components/',
        ]);
        return new TemplateView($renderingContext);
    }
}
