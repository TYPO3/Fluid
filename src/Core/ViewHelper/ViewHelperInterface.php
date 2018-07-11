<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface ViewHelperInterface
 *
 * Implemented by all ViewHelpers
 */
interface ViewHelperInterface
{

    /**
     * @return ArgumentDefinition[]
     */
    public function prepareArguments();

    /**
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments);

    /**
     * @param NodeInterface[] $nodes
     * @return void
     */
    public function setChildNodes(array $nodes);

    /**
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext);

    /**
     * Initialize the arguments of the ViewHelper, and call the render() method of the ViewHelper.
     *
     * @return string the rendered ViewHelper.
     */
    public function initializeArgumentsAndRender();

    /**
     * Initializes the view helper before invoking the render method.
     *
     * Override this method to solve tasks before the view helper content is rendered.
     *
     * @return void
     */
    public function initialize();

    /**
     * Helper method which triggers the rendering of everything between the
     * opening and the closing tag.
     *
     * @return mixed The finally rendered child nodes.
     */
    public function renderChildren();

    /**
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateArguments();

    /**
     * Initialize all arguments. You need to override this method and call
     * $this->registerArgument(...) inside this method, to register all your arguments.
     *
     * @return void
     */
    public function initializeArguments();

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array $arguments
     * @return void
     */
    public function handleAdditionalArguments(array $arguments);

    /**
     * Method which can be implemented in any ViewHelper if that ViewHelper desires
     * the ability to allow additional, undeclared, dynamic etc. arguments for the
     * node in the template. Do not implement this unless you need it!
     *
     * @param array $arguments
     * @return void
     */
    public function validateAdditionalArguments(array $arguments);

    /**
     * Here follows a more detailed description of the arguments of this function:
     *
     * $arguments contains a plain array of all arguments this ViewHelper has received,
     * including the default argument values if an argument has not been specified
     * in the ViewHelper invocation.
     *
     * $renderChildrenClosure is a closure you can execute instead of $this->renderChildren().
     * It returns the rendered child nodes, so you can simply do $renderChildrenClosure() to execute
     * it. It does not take any parameters.
     *
     * $renderingContext contains references to the VariableProvider and the
     * ViewHelperVariableContainer.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string the resulting string which is directly shown
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext);

    /**
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
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler);

    /**
     * Called when being inside a cached template.
     *
     * @param \Closure $renderChildrenClosure
     * @return void
     */
    public function setRenderChildrenClosure(\Closure $renderChildrenClosure);
}
