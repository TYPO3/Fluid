<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Node which will call a ViewHelper associated with this node.
 *
 * @internal
 * @todo Make class final.
 */
class ViewHelperNode extends AbstractNode
{
    /**
     * @var string
     */
    protected $viewHelperClassName;

    /**
     * @var NodeInterface[]
     */
    protected $arguments = [];

    /**
     * @var ViewHelperInterface
     */
    protected $uninitializedViewHelper;

    /**
     * @var ArgumentDefinition[]
     */
    protected $argumentDefinitions = [];

    /**
     * @var string
     */
    protected $pointerTemplateCode;

    /**
     * Constructor.
     *
     * @param RenderingContextInterface $renderingContext a RenderingContext, provided by invoker
     * @param string $namespace the namespace identifier of the ViewHelper.
     * @param string $identifier the name of the ViewHelper to render, inside the namespace provided.
     * @param NodeInterface[] $arguments Arguments of view helper - each value is a RootNode.
     */
    public function __construct(RenderingContextInterface $renderingContext, $namespace, $identifier, array $arguments)
    {
        $resolver = $renderingContext->getViewHelperResolver();
        $this->arguments = $arguments;
        $this->viewHelperClassName = $resolver->resolveViewHelperClassName($namespace, $identifier);
        $this->uninitializedViewHelper = $resolver->createViewHelperInstanceFromClassName($this->viewHelperClassName);
        $this->uninitializedViewHelper->setViewHelperNode($this);
        // Note: RenderingContext required here though replaced later. See https://github.com/TYPO3Fluid/Fluid/pull/93
        $this->uninitializedViewHelper->setRenderingContext($renderingContext);
        $this->argumentDefinitions = $resolver->getArgumentDefinitionsForViewHelper($this->uninitializedViewHelper);
    }

    /**
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitions()
    {
        return $this->argumentDefinitions;
    }

    /**
     * Returns the attached (but still uninitialized) ViewHelper for this ViewHelperNode.
     * We need this method because sometimes Interceptors need to ask some information from the ViewHelper.
     *
     * @return ViewHelperInterface
     */
    public function getUninitializedViewHelper()
    {
        return $this->uninitializedViewHelper;
    }

    /**
     * Get class name of view helper
     *
     * @return string Class Name of associated view helper
     */
    public function getViewHelperClassName()
    {
        return $this->viewHelperClassName;
    }

    /**
     * @internal only needed for compiling templates
     * @return NodeInterface[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $argumentName
     * @internal only needed for compiling templates
     * @return ArgumentDefinition
     */
    public function getArgumentDefinition($argumentName)
    {
        return $this->argumentDefinitions[$argumentName];
    }

    /**
     * @param NodeInterface $childNode
     */
    public function addChildNode(NodeInterface $childNode)
    {
        parent::addChildNode($childNode);
        $this->uninitializedViewHelper->setChildNodes($this->childNodes);
    }

    /**
     * @param string $pointerTemplateCode
     */
    public function setPointerTemplateCode($pointerTemplateCode)
    {
        $this->pointerTemplateCode = $pointerTemplateCode;
    }

    /**
     * Call the view helper associated with this object.
     *
     * First, it evaluates the arguments of the view helper.
     *
     * If the view helper implements \TYPO3Fluid\Fluid\Core\ViewHelper\ChildNodeAccessInterface,
     * it calls setChildNodes(array childNodes) on the view helper.
     *
     * Afterwards, checks that the view helper did not leave a variable lying around.
     *
     * @param RenderingContextInterface $renderingContext
     * @return string evaluated node after the view helper has been called.
     */
    public function evaluate(RenderingContextInterface $renderingContext)
    {
        return $renderingContext->getViewHelperInvoker()->invoke($this->uninitializedViewHelper, $this->arguments, $renderingContext);
    }

    public function convert(TemplateCompiler $templateCompiler): array
    {
        $initializationPhpCode = '// Rendering ViewHelper ' . $this->viewHelperClassName . chr(10);

        // Build up $arguments array
        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $renderChildrenClosureVariableName = $templateCompiler->variableName('renderChildrenClosure');
        $viewHelperInitializationPhpCode = '';

        try {
            $convertedViewHelperExecutionCode = $this->uninitializedViewHelper->compile(
                $argumentsVariableName,
                $renderChildrenClosureVariableName,
                $viewHelperInitializationPhpCode,
                $this,
                $templateCompiler
            );

            $accumulatedArgumentInitializationCode = '';
            $argumentInitializationCode = sprintf('%s = [' . chr(10), $argumentsVariableName);

            $arguments = $this->arguments;
            $argumentDefinitions = $this->argumentDefinitions;
            foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
                if (!array_key_exists($argumentName, $arguments)) {
                    // Argument *not* given to VH, use default value
                    $defaultValue = $argumentDefinition->getDefaultValue();
                    $argumentInitializationCode .= sprintf(
                        '\'%s\' => %s,' . chr(10),
                        $argumentName,
                        is_array($defaultValue) && empty($defaultValue) ? '[]' : var_export($defaultValue, true)
                    );
                } else {
                    // Argument *is* given to VH, resolve
                    $argumentValue = $arguments[$argumentName];
                    if ($argumentValue instanceof NodeInterface) {
                        $converted = $argumentValue->convert($templateCompiler);
                        if (!empty($converted['initialization'])) {
                            $accumulatedArgumentInitializationCode .= $converted['initialization'];
                        }
                        $argumentInitializationCode .= sprintf(
                            '\'%s\' => %s,' . chr(10),
                            $argumentName,
                            $converted['execution']
                        );
                    } else {
                        $argumentInitializationCode .= sprintf(
                            '\'%s\' => %s,' . chr(10),
                            $argumentName,
                            $argumentValue
                        );
                    }
                }
            }

            $argumentInitializationCode .= '];' . chr(10);

            // Build up closure which renders the child nodes
            $initializationPhpCode .= sprintf(
                '%s = %s;' . chr(10),
                $renderChildrenClosureVariableName,
                $templateCompiler->wrapChildNodesInClosure($this)
            );

            $initializationPhpCode .= $accumulatedArgumentInitializationCode . chr(10) . $argumentInitializationCode . $viewHelperInitializationPhpCode;
        } catch (StopCompilingChildrenException $stopCompilingChildrenException) {
            $convertedViewHelperExecutionCode = '\'' . str_replace("'", "\'", $stopCompilingChildrenException->getReplacementString()) . '\'';
        }
        $initializationArray = [
            'initialization' => $initializationPhpCode,
            // @todo: compile() *should* return strings, but it's not enforced in the interface.
            //        The string cast is here to stay compatible in case something still returns for instance null.
            'execution' => (string)$convertedViewHelperExecutionCode === '' ? "''" : $convertedViewHelperExecutionCode
        ];
        return $initializationArray;
    }
}
