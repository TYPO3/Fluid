<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Contract for FluidRenderer
 */
interface FluidRendererInterface
{
    public function renderSource(string $source);
    public function renderFile(string $filePathAndName);
    public function renderComponent(ComponentInterface $component);
    public function getComponentBeingRendered(): ?ComponentInterface;
    public function getRenderingContext(): RenderingContextInterface;
    public function setRenderingContext(RenderingContextInterface $renderingContext): void;
}
