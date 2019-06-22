<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\Generator;
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

/**
 * Class RenderingContextFixture
 */
class RenderingContextFixture implements RenderingContextInterface
{
    /**
     * @var ErrorHandlerInterface
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
     * @var boolean
     */
    public $cacheDisabled = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $mockBuilder = new Generator();
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
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler(): ErrorHandlerInterface
    {
        return isset($this->errorHandler) ? $this->errorHandler : new StandardErrorHandler();
    }

    /**
     * @param ErrorHandlerInterface $errorHandler
     * @return void
     */
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Injects the template variable container containing all variables available through ObjectAccessors
     * in the template
     *
     * @param VariableProviderInterface $variableProvider The template variable container to set
     */
    public function setVariableProvider(VariableProviderInterface $variableProvider): void
    {
        $this->variableProvider = $variableProvider;
    }

    /**
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     */
    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer): void
    {
        $this->viewHelperVariableContainer = $viewHelperVariableContainer;
    }

    /**
     * Get the template variable container
     *
     * @return VariableProviderInterface The Template Variable Container
     */
    public function getVariableProvider(): VariableProviderInterface
    {
        return $this->variableProvider;
    }

    /**
     * Get the ViewHelperVariableContainer
     *
     * @return ViewHelperVariableContainer
     */
    public function getViewHelperVariableContainer(): ViewHelperVariableContainer
    {
        return $this->viewHelperVariableContainer;
    }

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver(): ViewHelperResolver
    {
        return $this->viewHelperResolver;
    }

    /**
     * @param ViewHelperResolver $viewHelperResolver
     * @return void
     */
    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver): void
    {
        $this->viewHelperResolver = $viewHelperResolver;
    }

    /**
     * @return ViewHelperInvoker
     */
    public function getViewHelperInvoker(): ViewHelperInvoker
    {
        return $this->viewHelperInvoker;
    }

    /**
     * @param ViewHelperInvoker $viewHelperInvoker
     * @return void
     */
    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker): void
    {
        $this->viewHelperInvoker = $viewHelperInvoker;
    }

    /**
     * Inject the Template Parser
     *
     * @param TemplateParser $templateParser The template parser
     * @return void
     */
    public function setTemplateParser(TemplateParser $templateParser): void
    {
        $this->templateParser = $templateParser;
    }

    /**
     * @return TemplateParser
     */
    public function getTemplateParser(): TemplateParser
    {
        return $this->templateParser;
    }

    /**
     * @param TemplateCompiler $templateCompiler
     * @return void
     */
    public function setTemplateCompiler(TemplateCompiler $templateCompiler): void
    {
        $this->templateCompiler = $templateCompiler;
    }

    /**
     * @return TemplateCompiler
     */
    public function getTemplateCompiler(): TemplateCompiler
    {
        return $this->templateCompiler;
    }

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths(): TemplatePaths
    {
        return $this->templatePaths;
    }

    /**
     * @param TemplatePaths $templatePaths
     * @return void
     */
    public function setTemplatePaths(TemplatePaths $templatePaths): void
    {
        $this->templatePaths = $templatePaths;
    }

    /**
     * Delegation: Set the cache used by this View's compiler
     *
     * @param FluidCacheInterface $cache
     * @return void
     */
    public function setCache(FluidCacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @return FluidCacheInterface
     */
    public function getCache(): FluidCacheInterface
    {
        return $this->cache;
    }

    /**
     * @return boolean
     */
    public function isCacheEnabled(): bool
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
    public function setTemplateProcessors(array $templateProcessors): void
    {
        $this->templateProcessors = $templateProcessors;
    }

    /**
     * @return TemplateProcessorInterface[]
     */
    public function getTemplateProcessors(): array
    {
        return $this->templateProcessors;
    }

    /**
     * @return array
     */
    public function getExpressionNodeTypes(): array
    {
        return $this->expressionNodeTypes;
    }

    /**
     * @param array $expressionNodeTypes
     * @return void
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes): void
    {
        $this->expressionNodeTypes = $expressionNodeTypes;
    }

    /**
     * Build parser configuration
     *
     * @return Configuration
     */
    public function buildParserConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * @param string $controllerName
     * @return void
     */
    public function setControllerName(string $controllerName): void
    {
    }

    /**
     * @return string
     */
    public function getControllerAction(): string
    {
        return $this->controllerAction;
    }

    /**
     * @param string $action
     * @return void
     */
    public function setControllerAction(string $action): void
    {
        $this->controllerAction = $action;
    }
}
