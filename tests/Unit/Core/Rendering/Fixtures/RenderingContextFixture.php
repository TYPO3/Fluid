<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\Fixtures;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class RenderingContextFixture implements RenderingContextInterface
{
    /**
     * @var ErrorHandlerInterface|null
     */
    public $errorHandler;

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
     * @var bool
     */
    public $cacheDisabled = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $mockBuilder = new Generator();
        /** @var VariableProviderInterface&MockObject $variableProvider */
        $variableProvider = $mockBuilder->getMock(VariableProviderInterface::class);
        $this->variableProvider = $variableProvider;
        /** @var ViewHelperVariableContainer&MockObject $viewHelperVariableContainer */
        $viewHelperVariableContainer = $mockBuilder->getMock(ViewHelperVariableContainer::class, ['dummy']);
        $this->viewHelperVariableContainer = $viewHelperVariableContainer;
        /** @var ViewHelperResolver&MockObject $viewHelperResolver */
        $viewHelperResolver = $mockBuilder->getMock(ViewHelperResolver::class, ['dummy']);
        $this->viewHelperResolver = $viewHelperResolver;
        /** @var ViewHelperInvoker&MockObject $viewHelperInvoker */
        $viewHelperInvoker = $mockBuilder->getMock(ViewHelperInvoker::class, ['dummy']);
        $this->viewHelperInvoker = $viewHelperInvoker;
        /** @var TemplateParser&MockObject $templateParser */
        $templateParser = $mockBuilder->getMock(TemplateParser::class, ['dummy']);
        $this->templateParser = $templateParser;
        /** @var TemplateCompiler&MockObject $templateCompiler */
        $templateCompiler = $mockBuilder->getMock(TemplateCompiler::class, ['dummy']);
        $this->templateCompiler = $templateCompiler;
        /** @var TemplatePaths&MockObject $templatePaths */
        $templatePaths = $mockBuilder->getMock(TemplatePaths::class, ['dummy']);
        $this->templatePaths = $templatePaths;
        /** @var FluidCacheInterface&MockObject $cache */
        $cache = $mockBuilder->getMock(FluidCacheInterface::class);
        $this->cache = $cache;
    }

    /**
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler()
    {
        return $this->errorHandler ?? new StandardErrorHandler();
    }

    /**
     * @param ErrorHandlerInterface $errorHandler
     */
    public function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
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
     */
    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker)
    {
        $this->viewHelperInvoker = $viewHelperInvoker;
    }

    /**
     * Inject the Template Parser
     *
     * @param TemplateParser $templateParser The template parser
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
     */
    public function setTemplatePaths(TemplatePaths $templatePaths)
    {
        $this->templatePaths = $templatePaths;
    }

    /**
     * Delegation: Set the cache used by this View's compiler
     *
     * @param FluidCacheInterface $cache
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
     * @return bool
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
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
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
     */
    public function setControllerAction($action)
    {
        $this->controllerAction = $action;
    }
}
