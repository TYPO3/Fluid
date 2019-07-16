<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\MathExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\TernaryExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\EscapingModifierTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\PassthroughSourceModifierTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\ViewInterface;

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
     * @var ViewHelperInvoker
     */
    protected $viewHelperInvoker;

    /**
     * @var TemplatePaths
     */
    protected $templatePaths;

    /**
     * @var string|null
     */
    protected $controllerName;

    /**
     * @var string|null
     */
    protected $controllerAction;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var TemplateParser
     */
    protected $templateParser;

    /**
     * @var TemplateProcessorInterface[]
     */
    protected $templateProcessors = [];

    /**
     * List of class names implementing ExpressionNodeInterface
     * which will be consulted when an expression does not match
     * any built-in parser expression types.
     *
     * @var array
     */
    protected $expressionNodeTypes = [
        CastingExpressionNode::class,
        MathExpressionNode::class,
        TernaryExpressionNode::class,
    ];

    /**
     * Constructor
     *
     * Constructing a RenderingContext should result in an object containing instances
     * in all properties of the object. Subclassing RenderingContext allows changing the
     * types of instances that are created.
     *
     * Setters are used to fill the object instances. Some setters will call the
     * setRenderingContext() method (convention name) to provide the instance that is
     * created with an instance of the "parent" RenderingContext.
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
        $this->initialize();
    }

    public function initialize(): void
    {
        $this->setTemplateParser($this->templateParser ?? new TemplateParser());
        $this->setTemplatePaths($this->templatePaths ?? new TemplatePaths());
        $this->setTemplateProcessors(
            empty($this->templateProcessors) ?
            [
                new EscapingModifierTemplateProcessor(),
                new PassthroughSourceModifierTemplateProcessor(),
                new NamespaceDetectionTemplateProcessor()
            ] : $this->templateProcessors
        );
        $this->setViewHelperResolver($this->viewHelperResolver ?? new ViewHelperResolver());
        $this->setViewHelperInvoker($this->viewHelperInvoker ?? new ViewHelperInvoker());
        $this->setViewHelperVariableContainer($this->viewHelperVariableContainer ?? new ViewHelperVariableContainer());
        $this->setVariableProvider($this->variableProvider ?? new StandardVariableProvider());
        $this->setErrorHandler($this->errorHandler ?? new StandardErrorHandler());
    }

    /**
     * @return ErrorHandlerInterface
     */
    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler;
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
        return $this->variableProvider;
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
        return $this->viewHelperVariableContainer;
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
        $this->templateParser->setRenderingContext($this);
    }

    /**
     * @return TemplateParser
     */
    public function getTemplateParser(): TemplateParser
    {
        return $this->templateParser;
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
        foreach ($this->templateProcessors as $templateProcessor) {
            $templateProcessor->setRenderingContext($this);
        }
    }

    /**
     * @return TemplateProcessorInterface[]
     */
    public function getTemplateProcessors(): array
    {
        return $this->templateProcessors;
    }

    /**
     * @return string
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
    public function buildParserConfiguration()
    {
        $parserConfiguration = new Configuration();
        $escapeInterceptor = new Escape();
        $parserConfiguration->addEscapingInterceptor($escapeInterceptor);
        return $parserConfiguration;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName ?? 'Default';
    }

    /**
     * @param string $controllerName
     * @return void
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
        return $this->controllerAction ?? 'Default';
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
