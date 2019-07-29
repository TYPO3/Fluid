<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\CastViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\MathViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

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
     * @var TemplateParser
     */
    public $templateParser;

    /**
     * @var TemplatePaths
     */
    public $templatePaths;

    /**
     * @var FluidRenderer
     */
    public $renderer;

    /**
     * @var array
     */
    public $expressionNodeTypes = [
        MathViewHelper::class,
        CastViewHelper::class,
        IfViewHelper::class,
    ];

    /**
     * @var string
     */
    public $controllerName = 'Default';

    /**
     * @var string
     */
    public $controllerAction = 'Default';

    /**
     * @return FluidRenderer
     */
    public function getRenderer(): FluidRenderer
    {
        return $this->renderer ?? ($this->renderer = new FluidRenderer($this));
    }

    /**
     * @param FluidRenderer $renderer
     */
    public function setRenderer(FluidRenderer $renderer): void
    {
        $this->renderer = $renderer;
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
        return $this->variableProvider ?? ($this->variableProvider = new StandardVariableProvider());
    }

    /**
     * Get the ViewHelperVariableContainer
     *
     * @return ViewHelperVariableContainer
     */
    public function getViewHelperVariableContainer(): ViewHelperVariableContainer
    {
        return $this->viewHelperVariableContainer ?? ($this->viewHelperVariableContainer = new ViewHelperVariableContainer());
    }

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver(): ViewHelperResolver
    {
        return $this->viewHelperResolver ?? ($this->viewHelperResolver = new ViewHelperResolver());
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
        return $this->templateParser ?? ($this->templateParser = new TemplateParser($this));
    }

    /**
     * @return TemplatePaths
     */
    public function getTemplatePaths(): TemplatePaths
    {
        return $this->templatePaths ?? ($this->templatePaths = new TemplatePaths());
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
    public function getParserConfiguration(): Configuration
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
