<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Class RenderingContextFixture
 */
class RenderingContextFixture implements RenderingContextInterface
{

    /**
     * @var VariableProviderInterface
     */
    public $variableProvider;

    /**
     * @var ViewHelperVariableContainer
     */
    public $viewHelperVariableContainer;

    /**
     * @var ViewHelperResolver
     */
    public $viewHelperResolver;

    /**
     * @var ViewHelperInvoker
     */
    public $viewHelperInvoker;

    /**
     * @var TemplateParser
     */
    public $templateParser;

    /**
     * @var TemplateCompiler
     */
    public $templateCompiler;

    /**
     * @var TemplatePaths
     */
    public $templatePaths;

    /**
     * @var FluidCacheInterface
     */
    public $cache;

    /**
     * @var TemplateProcessorInterface[]
     */
    public $templateProcessors = [];

    /**
     * @var array
     */
    public $expressionNodeTypes = [];

    /**
     * @var string
     */
    public $controllerName = 'Default';

    /**
     * @var string
     */
    public $controllerAction = 'Default';

    /**
     * @var boolean
     */
    public $cacheDisabled = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $mockBuilder = new \PHPUnit_Framework_MockObject_Generator();
        $this->variableProvider = $mockBuilder->getMock(VariableProviderInterface::class);
        $this->viewHelperVariableContainer = $mockBuilder->getMock(ViewHelperVariableContainer::class, ['dummy']);
        $this->viewHelperResolver = $mockBuilder->getMock(ViewHelperResolver::class, ['dummy']);
        $this->viewHelperInvoker = $mockBuilder->getMock(ViewHelperInvoker::class, ['dummy']);
        $this->templateParser = $mockBuilder->getMock(TemplateParser::class, ['dummy']);
        $this->templateCompiler = $mockBuilder->getMock(TemplateCompiler::class, ['dummy']);
        $this->templatePaths = $mockBuilder->getMock(TemplatePaths::class, ['dummy']);
        $this->cache = $mockBuilder->getMock(FluidCacheInterface::class);
    }

    /**
     * Injects the template variable container containing all variables available through ObjectAccessors
     * in the template
     *
     * @param VariableProviderInterface $variableProvider The template variable container to set
     */
    public function setVariableProvider(VariableProviderInterface $variableProvider)
    {
        $this->variableProvider = $variableProvider;
    }

    /**
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     */
    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer)
    {
        $this->viewHelperVariableContainer = $viewHelperVariableContainer;
    }

    /**
     * Get the template variable container
     *
     * @return VariableProviderInterface The Template Variable Container
     */
    public function getVariableProvider()
    {
        return $this->variableProvider;
    }

    /**
     * Get the ViewHelperVariableContainer
     *
     * @return ViewHelperVariableContainer
     */
    public function getViewHelperVariableContainer()
    {
        return $this->viewHelperVariableContainer;
    }

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver()
    {
        return $this->viewHelperResolver;
    }

    /**
     * @param ViewHelperResolver $viewHelperResolver
     * @return void
     */
    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver)
    {
        $this->viewHelperResolver = $viewHelperResolver;
    }

    /**
     * @return ViewHelperInvoker
     */
    public function getViewHelperInvoker()
    {
        return $this->viewHelperInvoker;
    }

    /**
     * @param ViewHelperInvoker $viewHelperInvoker
     * @return void
     */
    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker)
    {
        $this->viewHelperInvoker = $viewHelperInvoker;
    }

    /**
     * Inject the Template Parser
     *
     * @param TemplateParser $templateParser The template parser
     * @return void
     */
    public function setTemplateParser(TemplateParser $templateParser)
    {
        $this->templateParser = $templateParser;
    }

    /**
     * @return TemplateParser
     */
    public function getTemplateParser()
    {
        return $this->templateParser;
    }

    /**
     * @param TemplateCompiler $templateCompiler
     * @return void
     */
    public function setTemplateCompiler(TemplateCompiler $templateCompiler)
    {
        $this->templateCompiler = $templateCompiler;
    }

    /**
     * @return TemplateCompiler
     */
    public function getTemplateCompiler()
    {
        return $this->templateCompiler;
    }

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }

    /**
     * @param TemplatePaths $templatePaths
     * @return void
     */
    public function setTemplatePaths(TemplatePaths $templatePaths)
    {
        $this->templatePaths = $templatePaths;
    }

    /**
     * Delegation: Set the cache used by this View's compiler
     *
     * @param FluidCacheInterface $cache
     * @return void
     */
    public function setCache(FluidCacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return FluidCacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return !$this->cacheDisabled;
    }

    /**
     * Delegation: Set TemplateProcessor instances in the parser
     * through a public API.
     *
     * @param TemplateProcessorInterface[] $templateProcessors
     * @return void
     */
    public function setTemplateProcessors(array $templateProcessors)
    {
        $this->templateProcessors = $templateProcessors;
    }

    /**
     * @return TemplateProcessorInterface[]
     */
    public function getTemplateProcessors()
    {
        return $this->templateProcessors;
    }

    /**
     * @return array
     */
    public function getExpressionNodeTypes()
    {
        return $this->expressionNodeTypes;
    }

    /**
     * @param array $expressionNodeTypes
     * @return void
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes)
    {
        $this->expressionNodeTypes = $expressionNodeTypes;
    }

    /**
     * Build parser configuration
     *
     * @return Configuration
     */
    public function buildParserConfiguration()
    {
        return new Configuration();
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * @param string $controllerName
     * @return void
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName;
    }

    /**
     * @return string
     */
    public function getControllerAction()
    {
        return $this->controllerAction;
    }

    /**
     * @param string $action
     * @return void
     */
    public function setControllerAction($action)
    {
        $this->controllerAction = $action;
    }
}
