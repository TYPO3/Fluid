<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * The abstract base class for all view helpers.
 *
 * @api
 * @todo add missing types with Fluid v5
 */
abstract class AbstractViewHelper implements ViewHelperInterface
{
    /**
     * Stores all \TYPO3Fluid\Fluid\ArgumentDefinition instances
     * @var ArgumentDefinition[]
     */
    protected $argumentDefinitions = [];

    /**
     * Cache of argument definitions; the key is the ViewHelper class name, and the
     * value is the array of argument definitions.
     *
     * In our benchmarks, this cache leads to a 40% improvement when using a certain
     * ViewHelper class many times throughout the rendering process.
     * @var array
     */
    private static array $argumentDefinitionCache = [];

    /**
     * Current view helper node
     * @var ViewHelperNode
     */
    protected $viewHelperNode;

    /**
     * @var array<string, mixed>
     * @api
     */
    protected $arguments = [];

    /**
     * @var NodeInterface[] array
     * @api
     */
    protected $childNodes = [];

    /**
     * Current variable container reference.
     * @var VariableProviderInterface
     * @api
     */
    protected $templateVariableContainer;

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * Stores rendering contexts in a situation where ViewHelpers are called recursively from inside
     * one of their child nodes. In that case, the rendering context can change during the recursion,
     * but needs to be restored properly after each run. Thus, we store a stack of rendering contexts
     * to be able to restore the initial state of the ViewHelper.
     *
     * @var RenderingContextInterface[]
     */
    protected array $renderingContextStack = [];

    /**
     * @var \Closure
     */
    protected $renderChildrenClosure;

    /**
     * ViewHelper Variable Container
     * @var ViewHelperVariableContainer
     * @api
     */
    protected $viewHelperVariableContainer;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     * @see isChildrenEscapingEnabled()
     *
     * Note: If this is null, the value of $this->escapingInterceptorEnabled is considered for backwards compatibility.
     *
     * @var bool
     * @api
     */
    protected $escapeChildren;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     * @see isOutputEscapingEnabled()
     *
     * @var bool
     * @api
     */
    protected $escapeOutput;

    /**
     * @param array<string, mixed> $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext)
    {
        $this->renderingContext = $renderingContext;
        $this->templateVariableContainer = $renderingContext->getVariableProvider();
        $this->viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
    }

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeChildren instead!
     *
     * @return bool
     */
    public function isChildrenEscapingEnabled()
    {
        if ($this->escapeChildren === null) {
            // Disable children escaping automatically, if output escaping is on anyway.
            return !$this->isOutputEscapingEnabled();
        }
        return $this->escapeChildren;
    }

    /**
     * Returns whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     *
     * Note: This method is no public API, use $this->escapeOutput instead!
     *
     * @return bool
     */
    public function isOutputEscapingEnabled()
    {
        return $this->escapeOutput !== false;
    }

    /**
     * Returns the name of variable that contains the value to use instead of render children closure, if specified.
     * ViewHelpers that want to use contentArgumentName are expected to override this method with their own implementation.
     *
     * @api
     */
    public function getContentArgumentName(): ?string
    {
        return null;
    }

    /**
     * Register a new argument. Call this method from your ViewHelper subclass
     * inside the initializeArguments() method. If an argument with the same name
     * is already defined, it will be overridden.
     *
     * @param string $name Name of the argument
     * @param string $type Type of the argument
     * @param string $description Description of the argument
     * @param bool $required If true, argument is required. Defaults to false.
     * @param mixed $defaultValue Default value of argument. Will be used if the argument is not set.
     * @param bool|null $escape Can be toggled to true to force escaping of variables and inline syntax passed as argument value.
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper $this, to allow chaining.
     * @api
     */
    protected function registerArgument($name, $type, $description, $required = false, $defaultValue = null, $escape = null)
    {
        $this->argumentDefinitions[$name] = new ArgumentDefinition($name, $type, $description, $required, $defaultValue, $escape);
        return $this;
    }

    /**
     * Overrides a registered argument. Call this method from your ViewHelper subclass
     * inside the initializeArguments() method if you want to override a previously registered argument.
     * @see registerArgument()
     *
     * @param string $name Name of the argument
     * @param string $type Type of the argument
     * @param string $description Description of the argument
     * @param bool $required If true, argument is required. Defaults to false.
     * @param mixed $defaultValue Default value of argument
     * @param bool|null $escape Can be toggled to true to force escaping of variables and inline syntax passed as argument value.
     * @return \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper $this, to allow chaining.
     * @throws Exception
     * @api
     * @deprecated Will be removed in v5. No longer necessary since self::registerArgument() now allows overriding
     */
    protected function overrideArgument($name, $type, $description, $required = false, $defaultValue = null, $escape = null)
    {
        trigger_error('overrideArgument() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        if (!array_key_exists($name, $this->argumentDefinitions)) {
            throw new Exception(
                'Argument "' . $name . '" has not been defined, thus it can\'t be overridden.',
                1279212461,
            );
        }
        $this->argumentDefinitions[$name] = new ArgumentDefinition($name, $type, $description, $required, $defaultValue, $escape);
        return $this;
    }

    /**
     * Sets all needed attributes needed for the rendering. Called by the
     * framework. Populates $this->viewHelperNode.
     * @param ViewHelperNode $node View Helper node to be set.
     * @internal
     */
    public function setViewHelperNode(ViewHelperNode $node)
    {
        $this->viewHelperNode = $node;
    }

    /**
     * Sets all needed attributes needed for the rendering. Called by the
     * framework. Populates $this->viewHelperNode.
     * @param NodeInterface[] $childNodes
     * @internal
     */
    public function setChildNodes(array $childNodes)
    {
        $this->childNodes = $childNodes;
    }

    /**
     * Called when being inside a cached template.
     *
     * @param \Closure $renderChildrenClosure
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure)
    {
        $this->renderChildrenClosure = $renderChildrenClosure;
    }

    /**
     * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
     *
     * @return string the rendered ViewHelper.
     */
    public function initializeArgumentsAndRender()
    {
        $this->validateArguments();
        $this->initialize();

        return $this->render();
    }

    /**
     * Initializes the view helper before invoking the render method.
     *
     * Override this method to solve tasks before the view helper content is rendered.
     *
     * @api
     */
    public function initialize() {}

    /**
     * Helper method which triggers the rendering of everything between the
     * opening and the closing tag.
     *
     * @return mixed The finally rendered child nodes.
     * @api
     */
    public function renderChildren()
    {
        return $this->buildRenderChildrenClosure()();
    }

    /**
     * Creates a closure that renders a view helper's child nodes. It also takes
     * into account the contentArgumentName, which if defined leads to that argument
     * being rendered instead.
     *
     * No public API yet.
     *
     * @return \Closure
     */
    protected function buildRenderChildrenClosure()
    {
        $contentArgumentName = $this->getContentArgumentName();
        if ($contentArgumentName !== null && isset($this->arguments[$contentArgumentName])) {
            return fn() => $this->arguments[$contentArgumentName];
        }
        if ($this->renderChildrenClosure !== null) {
            return $this->renderChildrenClosure;
        }
        return function () {
            $this->renderingContextStack[] = $this->renderingContext;
            $result = $this->viewHelperNode->evaluateChildNodes($this->renderingContext);
            $this->setRenderingContext(array_pop($this->renderingContextStack));
            return $result;
        };
    }

    /**
     * Initialize all arguments and return them
     *
     * @return ArgumentDefinition[]
     */
    public function prepareArguments()
    {
        $thisClassName = get_class($this);
        if (isset(self::$argumentDefinitionCache[$thisClassName])) {
            $this->argumentDefinitions = self::$argumentDefinitionCache[$thisClassName];
        } else {
            $this->initializeArguments();
            self::$argumentDefinitionCache[$thisClassName] = $this->argumentDefinitions;
        }
        return $this->argumentDefinitions;
    }

    /**
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @throws \InvalidArgumentException
     */
    public function validateArguments()
    {
        $argumentDefinitions = $this->prepareArguments();
        foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
            if ($this->hasArgument($argumentName)) {
                $value = $this->arguments[$argumentName];
                $type = $registeredArgument->getType();
                if ($value !== $registeredArgument->getDefaultValue() && $type !== 'mixed') {
                    $givenType = is_object($value) ? get_class($value) : gettype($value);
                    if (!$this->isValidType($type, $value)) {
                        throw new \InvalidArgumentException(
                            'The argument "' . $argumentName . '" was registered with type "' . $type . '", but is of type "' .
                            $givenType . '" in view helper "' . get_class($this) . '".',
                            1256475113,
                        );
                    }
                }
            }
        }
    }

    /**
     * Check whether the defined type matches the value type
     *
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    protected function isValidType($type, $value)
    {
        if ($type === 'object') {
            if (!is_object($value)) {
                return false;
            }
        } elseif ($type === 'array' || substr($type, -2) === '[]') {
            if (!is_array($value) && !$value instanceof \ArrayAccess && !$value instanceof \Traversable && !empty($value)) {
                return false;
            }
            if (substr($type, -2) === '[]') {
                $firstElement = $this->getFirstElementOfNonEmpty($value);
                if ($firstElement === null) {
                    return true;
                }
                return $this->isValidType(substr($type, 0, -2), $firstElement);
            }
        } elseif ($type === 'string') {
            if (is_object($value) && !method_exists($value, '__toString')) {
                return false;
            }
        } elseif ($type === 'boolean' && !is_bool($value)) {
            return false;
        } elseif (class_exists($type) && $value !== null && !$value instanceof $type) {
            return false;
        } elseif (is_object($value) && !is_a($value, $type, true)) {
            return false;
        }
        return true;
    }

    /**
     * Return the first element of the given array, ArrayAccess or Traversable
     * that is not empty
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getFirstElementOfNonEmpty($value)
    {
        if (is_array($value)) {
            return reset($value);
        }
        if ($value instanceof \Traversable) {
            foreach ($value as $element) {
                return $element;
            }
        }
        return null;
    }

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @api
     */
    public function initializeArguments() {}

    /**
     * Tests if the given $argumentName is set, and not null.
     * The isset() test used fills both those requirements.
     *
     * @param string $argumentName
     * @return bool true if $argumentName is found
     * @api
     */
    protected function hasArgument($argumentName)
    {
        return isset($this->arguments[$argumentName]);
    }

    /**
     * Default implementation of "handling" additional, undeclared arguments.
     * In this implementation the behavior is to consistently throw an error
     * about NOT supporting any additional arguments. This method MUST be
     * overridden by any ViewHelper that desires this support and this inherited
     * method must not be called, obviously.
     *
     * @throws Exception
     * @param array<string, mixed> $arguments
     */
    public function handleAdditionalArguments(array $arguments) {}

    /**
     * Default implementation of validating additional, undeclared arguments.
     * In this implementation the behavior is to consistently throw an error
     * about NOT supporting any additional arguments. This method MUST be
     * overridden by any ViewHelper that desires this support and this inherited
     * method must not be called, obviously.
     *
     * @throws Exception
     * @param array<string, mixed> $arguments
     */
    public function validateAdditionalArguments(array $arguments)
    {
        if (!empty($arguments)) {
            throw new Exception(
                sprintf(
                    'Undeclared arguments passed to ViewHelper %s: %s. Valid arguments are: %s',
                    get_class($this),
                    implode(', ', array_keys($arguments)),
                    implode(', ', array_keys($this->argumentDefinitions)),
                ),
            );
        }
    }

    /**
     * Main render method of the ViewHelper. Every modern ViewHelper implementation
     * must implement this method.
     *
     * @todo Remove fallback implementation for renderStatic() and declare as abstract with Fluid v5
     *
     * @return mixed
     */
    public function render()
    {
        if (!method_exists(static::class, 'renderStatic')) {
            throw new Exception(
                sprintf(
                    'ViewHelper class "%s" does not declare a "render()" method. Also, no implementation of "renderStatic"' .
                    'could be found to use as fallback. Please implement "render()" on your ViewHelper class',
                    static::class,
                ),
            );
        }

        // This covers the edge case where a ViewHelper implements neither CompileWithRenderStatic nor
        // CompileWithContentArgumentAndRenderStatic, but still uses renderStatic().
        trigger_error('renderStatic() has been deprecated and will be removed in Fluid v5.', E_USER_DEPRECATED);
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext,
        );
    }

    /**
     * You only should override this method *when you absolutely know what you
     * are doing*, and really want to influence the generated PHP code during
     * template compilation directly.
     *
     * This method is called on compilation time.
     *
     * It has to return a *single* PHP statement without semi-colon or newline
     * at the end, which will be embedded at various places.
     *
     * Furthermore, it can append PHP code to the variable $initializationPhpCode.
     * In this case, all statements have to end with semi-colon and newline.
     *
     * Outputting new variables
     * ========================
     * If you want create a new PHP variable, you need to use
     * $templateCompiler->variableName('nameOfVariable') for this, as all variables
     * need to be globally unique.
     *
     * Return Value
     * ============
     * Besides returning a single string, it can also return the constant
     * \TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler::SHOULD_GENERATE_VIEWHELPER_INVOCATION
     * which means that after the $initializationPhpCode, the ViewHelper invocation
     * is built as normal. This is especially needed if you want to build new arguments
     * at run-time, as it is done for the AbstractConditionViewHelper.
     *
     * @param string $argumentsName Name of the variable in which the ViewHelper arguments are stored
     * @param string $closureName Name of the closure which can be executed to render the child nodes
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler)
    {
        $execution = sprintf(
            '$renderingContext->getViewHelperInvoker()->invoke(%s::class, %s, $renderingContext, %s)',
            static::class,
            $argumentsName,
            $closureName,
        );

        $contentArgumentName = $this->getContentArgumentName();
        if ($contentArgumentName !== null) {
            $initializationPhpCode .= sprintf(
                '%s = (%s[\'%s\'] !== null) ? function() use (%s) { return %s[\'%s\']; } : %s;',
                $closureName,
                $argumentsName,
                $contentArgumentName,
                $argumentsName,
                $argumentsName,
                $contentArgumentName,
                $closureName,
            );
        }

        return $execution;
    }

    /**
     * Save the associated ViewHelper node in a static public class variable.
     * called directly after the ViewHelper was built.
     *
     * @param ViewHelperNode $node
     * @param array<string, TextNode> $arguments
     * @param VariableProviderInterface $variableContainer
     */
    public static function postParseEvent(ViewHelperNode $node, array $arguments, VariableProviderInterface $variableContainer) {}

    /**
     * Resets the ViewHelper state.
     *
     * Overwrite this method if you need to get a clean state of your ViewHelper.
     */
    public function resetState() {}

    /**
     * @internal See interface description.
     */
    public function convert(TemplateCompiler $templateCompiler): array
    {
        $initializationPhpCode = '// Rendering ViewHelper ' . $this->viewHelperNode->getViewHelperClassName() . chr(10);

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $renderChildrenClosureVariableName = $templateCompiler->variableName('renderChildrenClosure');
        $viewHelperInitializationPhpCode = '';

        $convertedViewHelperExecutionCode = $this->compile(
            $argumentsVariableName,
            $renderChildrenClosureVariableName,
            $viewHelperInitializationPhpCode,
            $this->viewHelperNode,
            $templateCompiler,
        );

        $accumulatedArgumentInitializationCode = '';
        $argumentInitializationCode = sprintf('%s = [' . chr(10), $argumentsVariableName);

        $arguments = $this->viewHelperNode->getArguments();
        $argumentDefinitions = $this->viewHelperNode->getArgumentDefinitions();
        foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
            if (!array_key_exists($argumentName, $arguments)) {
                // Argument *not* given to VH, use default value
                $defaultValue = $argumentDefinition->getDefaultValue();
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    is_array($defaultValue) && empty($defaultValue) ? '[]' : var_export($defaultValue, true),
                );
            }
        }

        foreach ($arguments as $argumentName => $argumentValue) {
            if ($argumentValue instanceof NodeInterface) {
                $converted = $argumentValue->convert($templateCompiler);
                if (!empty($converted['initialization'])) {
                    $accumulatedArgumentInitializationCode .= $converted['initialization'];
                }
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $converted['execution'],
                );
            } else {
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $argumentValue,
                );
            }
        }

        $argumentInitializationCode .= '];' . chr(10);

        // Build up closure which renders the child nodes
        $initializationPhpCode .= sprintf(
            '%s = %s;' . chr(10),
            $renderChildrenClosureVariableName,
            $templateCompiler->wrapChildNodesInClosure($this->viewHelperNode),
        );

        $initializationPhpCode .= $accumulatedArgumentInitializationCode . chr(10) . $argumentInitializationCode . $viewHelperInitializationPhpCode;
        return [
            'initialization' => $initializationPhpCode,
            // @todo: compile() *should* return strings, but it's not enforced in the interface.
            //        The string cast is here to stay compatible in case something still returns for instance null.
            'execution' => (string)$convertedViewHelperExecutionCode === '' ? "''" : $convertedViewHelperExecutionCode,
        ];
    }
}
