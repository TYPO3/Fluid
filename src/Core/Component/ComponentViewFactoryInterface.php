<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplateAwareViewInterface;

/**
 * @internal This interface should only be used for type hinting
 */
interface ComponentViewFactoryInterface
{
    public function createView(RenderingContextInterface $renderingContext): TemplateAwareViewInterface;

    public function prepareTemplateName(string $viewHelperName): string;
}
