<?php
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Contract for the rendering context
 */
interface RenderingContextInterface
{
    /**
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler(): ErrorHandlerInterface;

    /**
     * @param ErrorHandlerInterface $errorHandler
     * @return void
     */
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void;

    /**
     * Injects the template variable container containing all variables available through ObjectAccessors
     * in the template
     *
     * @param VariableProviderInterface $variableProvider The template variable container to set
     */
    public function setVariableProvider(VariableProviderInterface $variableProvider);

    /**
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     */
    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer);

    /**
     * Get the template variable container
     *
     * @return VariableProviderInterface The Template Variable Container
     */
    public function getVariableProvider(): VariableProviderInterface;

    /**
     * Get the ViewHelperVariableContainer
     *
     * @return ViewHelperVariableContainer
     */
    public function getViewHelperVariableContainer(): ViewHelperVariableContainer;

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver(): ViewHelperResolver;

    /**
     * @param ViewHelperResolver $viewHelperResolver
     * @return void
     */
    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver): void;

    /**
     * @return ViewHelperInvoker
     */
    public function getViewHelperInvoker(): ViewHelperInvoker;

    /**
     * @param ViewHelperInvoker $viewHelperInvoker
     * @return void
     */
    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker): void;

    /**
     * Inject the Template Parser
     *
     * @param TemplateParser $templateParser The template parser
     * @return void
     */
    public function setTemplateParser(TemplateParser $templateParser): void;

    /**
     * @return TemplateParser
     */
    public function getTemplateParser(): TemplateParser;

    /**
     * @param TemplateCompiler $templateCompiler
     * @return void
     */
    public function setTemplateCompiler(TemplateCompiler $templateCompiler): void;

    /**
     * @return TemplateCompiler
     */
    public function getTemplateCompiler(): TemplateCompiler;

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths(): TemplatePaths;

    /**
     * @param TemplatePaths $templatePaths
     * @return void
     */
    public function setTemplatePaths(TemplatePaths $templatePaths): void;

    /**
     * Delegation: Set the cache used by this View's compiler
     *
     * @param FluidCacheInterface $cache
     * @return void
     */
    public function setCache(FluidCacheInterface $cache): void;

    /**
     * @return FluidCacheInterface
     */
    public function getCache(): FluidCacheInterface;

    /**
     * @return boolean
     */
    public function isCacheEnabled(): bool;

    /**
     * Delegation: Set TemplateProcessor instances in the parser
     * through a public API.
     *
     * @param TemplateProcessorInterface[] $templateProcessors
     * @return void
     */
    public function setTemplateProcessors(array $templateProcessors): void;

    /**
     * @return TemplateProcessorInterface[]
     */
    public function getTemplateProcessors(): array;

    /**
     * @return array
     */
    public function getExpressionNodeTypes(): array;

    /**
     * @param array $expressionNodeTypes
     * @return void
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes): void;

    /**
     * Build parser configuration
     *
     * @return Configuration
     */
    public function buildParserConfiguration(): Configuration;

    /**
     * @return string
     */
    public function getControllerName(): string;

    /**
     * @param string $controllerName
     * @return void
     */
    public function setControllerName(string $controllerName): void;

    /**
     * @return string
     */
    public function getControllerAction(): string;

    /**
     * @param string $action
     * @return void
     */
    public function setControllerAction(string $action): void;
}
