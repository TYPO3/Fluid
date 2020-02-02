<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\CastViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\MathViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper;

/**
 * The rendering context that contains useful information during rendering time of a Fluid template
 */
class RenderingContext implements RenderingContextInterface
{
    /**
     * @var ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * Template Variable Container. Contains all variables available through object accessors in the template
     *
     * @var VariableProviderInterface
     */
    protected $variableProvider;

    /**
     * ViewHelper Variable Container
     *
     * @var ViewHelperVariableContainer
     */
    protected $viewHelperVariableContainer;

    /**
     * @var ViewHelperResolver
     */
    protected $viewHelperResolver;

    /**
     * @var TemplatePaths
     */
    protected $templatePaths;

    /**
     * @var TemplateParser
     */
    protected $templateParser;

    /**
     * @var FluidRendererInterface
     */
    protected $renderer;

    /**
     * @var Configuration
     */
    protected $parserConfiguration;

    /**
     * List of class names implementing ExpressionNodeInterface
     * which will be consulted when an expression does not match
     * any built-in parser expression types.
     *
     * @var array
     */
    protected $expressionNodeTypes = [
        CastViewHelper::class,
        MathViewHelper::class,
        IfViewHelper::class,
    ];

    public function __construct()
    {
        $this->viewHelperVariableContainer = new ViewHelperVariableContainer();
        $this->viewHelperResolver = new ViewHelperResolver($this);
        $this->variableProvider = new StandardVariableProvider([]);
        $this->templatePaths = new TemplatePaths();
    }

    /**
     * @return FluidRendererInterface
     */
    public function getRenderer(): FluidRendererInterface
    {
        return $this->renderer ?? ($this->renderer = new FluidRenderer($this));
    }

    /**
     * @param FluidRendererInterface $renderer
     */
    public function setRenderer(FluidRendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler ?? ($this->errorHandler = new StandardErrorHandler());
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
     * Get the template variable container
     *
     * @return VariableProviderInterface The Template Variable Container
     */
    public function getVariableProvider(): VariableProviderInterface
    {
        return $this->variableProvider ?? ($this->variableProvider = new StandardVariableProvider());
    }

    /**
     * @return ViewHelperResolver
     */
    public function getViewHelperResolver(): ViewHelperResolver
    {
        return $this->viewHelperResolver ?? ($this->viewHelperResolver = new ViewHelperResolver($this));
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
     * Set the ViewHelperVariableContainer
     *
     * @param ViewHelperVariableContainer $viewHelperVariableContainer
     * @return void
     */
    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer): void
    {
        $this->viewHelperVariableContainer = $viewHelperVariableContainer;
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
        if (!isset($this->parserConfiguration)) {
            $this->parserConfiguration = new Configuration();
        }
        return $this->parserConfiguration;
    }
}
