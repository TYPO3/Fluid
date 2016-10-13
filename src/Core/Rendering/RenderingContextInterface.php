<?php
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
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
    public function getVariableProvider();

    /**
     * Get the ViewHelperVariableContainer
     *
     * @return ViewHelperVariableContainer
     */
    public function getViewHelperVariableContainer();

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver();

    /**
     * @param ViewHelperResolver $viewHelperResolver
     * @return void
     */
    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver);

    /**
     * @return ViewHelperInvoker
     */
    public function getViewHelperInvoker();

    /**
     * @param ViewHelperInvoker $viewHelperInvoker
     * @return void
     */
    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker);

    /**
     * Inject the Template Parser
     *
     * @param TemplateParser $templateParser The template parser
     * @return void
     */
    public function setTemplateParser(TemplateParser $templateParser);

    /**
     * @return TemplateParser
     */
    public function getTemplateParser();

    /**
     * @param TemplateCompiler $templateCompiler
     * @return void
     */
    public function setTemplateCompiler(TemplateCompiler $templateCompiler);

    /**
     * @return TemplateCompiler
     */
    public function getTemplateCompiler();

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths();

    /**
     * @param TemplatePaths $templatePaths
     * @return void
     */
    public function setTemplatePaths(TemplatePaths $templatePaths);

    /**
     * Delegation: Set the cache used by this View's compiler
     *
     * @param FluidCacheInterface $cache
     * @return void
     */
    public function setCache(FluidCacheInterface $cache);

    /**
     * @return FluidCacheInterface
     */
    public function getCache();

    /**
     * @return boolean
     */
    public function isCacheEnabled();

    /**
     * Delegation: Set TemplateProcessor instances in the parser
     * through a public API.
     *
     * @param TemplateProcessorInterface[] $templateProcessors
     * @return void
     */
    public function setTemplateProcessors(array $templateProcessors);

    /**
     * @return TemplateProcessorInterface[]
     */
    public function getTemplateProcessors();

    /**
     * @return array
     */
    public function getExpressionNodeTypes();

    /**
     * @param array $expressionNodeTypes
     * @return void
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes);

    /**
     * Build parser configuration
     *
     * @return Configuration
     */
    public function buildParserConfiguration();

    /**
     * @return string
     */
    public function getControllerName();

    /**
     * @param string $controllerName
     * @return void
     */
    public function setControllerName($controllerName);

    /**
     * @return string
     */
    public function getControllerAction();

    /**
     * @param string $action
     * @return void
     */
    public function setControllerAction($action);
}
