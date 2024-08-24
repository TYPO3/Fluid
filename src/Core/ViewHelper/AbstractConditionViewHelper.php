<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This view helper is an abstract ViewHelper which implements an if/else condition.
 *
 * = Usage =
 *
 * To create a custom Condition ViewHelper, you need to subclass this class, and
 * implement your own render() method. Inside there, you should call $this->renderThenChild()
 * if the condition evaluated to true, and $this->renderElseChild() if the condition evaluated
 * to false.
 *
 * Every Condition ViewHelper has a "then" and "else" argument, so it can be used like:
 * <[aConditionViewHelperName] .... then="condition true" else="condition false" />,
 * or as well use the "then" and "else" child nodes.
 *
 * @see \TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper for a more detailed explanation and a simple usage example.
 *
 * @api
 * @todo add missing types with Fluid v5
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    private ?\Closure $thenClosure = null;
    private ?\Closure $elseClosure = null;

    /**
     * @var array<array{'condition': \Closure, 'body': \Closure}>
     */
    private array $elseIfClosures = [];

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', false, null, true);
        $this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', false, null, true);
    }

    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     * Method which only gets called if the template is not compiled. For static calling,
     * the then/else nodes are converted to closures and condition evaluation closures.
     *
     * @return mixed
     * @api
     */
    public function render()
    {
        if (static::verdict($this->arguments, $this->renderingContext)) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

    /**
     * Static method which can be overridden by subclasses. If a subclass
     * requires a different (or faster) decision then this method is the one
     * to override and implement.
     *
     * @param array<string, mixed> $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return isset($arguments['condition']) && (bool)($arguments['condition']);
    }

    /**
     * Returns value of "then" attribute.
     * If then attribute is not set, iterates through child nodes and renders ThenViewHelper.
     * If then attribute is not set and no ThenViewHelper and no ElseViewHelper is found, all child nodes are rendered
     *
     * @return mixed rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
     * @api
     */
    protected function renderThenChild()
    {
        // Prefer "then" ViewHelper argument if present
        if ($this->hasArgument('then')) {
            return $this->arguments['then'];
        }

        // Closure might be present if ViewHelper is called from a cached template
        if ($this->thenClosure !== null) {
            return ($this->thenClosure)();
        }

        // The following code can only be evaluated for uncached templates where the node structure
        // is still available. If it's not, it has already been executed during compilation and we can
        // assume that the condition wasn't met
        if (!$this->viewHelperNode instanceof ViewHelperNode) {
            return '';
        }

        $elseViewHelperEncountered = false;
        foreach ($this->viewHelperNode->getChildNodes() as $childNode) {
            if ($childNode instanceof ViewHelperNode
                && substr($childNode->getViewHelperClassName(), -14) === 'ThenViewHelper') {
                $data = $childNode->evaluate($this->renderingContext);
                return $data;
            }
            if ($childNode instanceof ViewHelperNode
                && substr($childNode->getViewHelperClassName(), -14) === 'ElseViewHelper') {
                $elseViewHelperEncountered = true;
            }
        }

        if ($elseViewHelperEncountered) {
            return '';
        }
        return $this->renderChildren();
    }

    /**
     * Returns value of "else" attribute.
     * If else attribute is not set, iterates through child nodes and renders ElseViewHelper.
     * If else attribute is not set and no ElseViewHelper is found, an empty string will be returned.
     *
     * @return mixed rendered ElseViewHelper or an empty string if no ThenViewHelper was found
     * @api
     */
    protected function renderElseChild()
    {
        // Closures are present if ViewHelper is called from a cached template
        if ($this->elseIfClosures !== []) {
            // Check each "f:else if" by evaluating its "condition" closure; evaluate and return
            // the "body" closure if condition is met
            foreach ($this->elseIfClosures as $elseIf) {
                if ($elseIf['condition']()) {
                    return $elseIf['body']();
                }
            }
        }

        // Prefer "else" ViewHelper argument if present
        if ($this->hasArgument('else')) {
            return $this->arguments['else'];
        }

        // Closure might be present if ViewHelper is called from a cached template
        if ($this->elseClosure !== null) {
            return ($this->elseClosure)();
        }

        // The following code can only be evaluated for uncached templates where the node structure
        // is still available. If it's not, it has already been executed during compilation and we can
        // assume that the condition wasn't met
        if (!$this->viewHelperNode instanceof ViewHelperNode) {
            return '';
        }

        /** @var ViewHelperNode|null $elseNode */
        $elseNode = null;
        foreach ($this->viewHelperNode->getChildNodes() as $childNode) {
            if ($childNode instanceof ViewHelperNode
                && substr($childNode->getViewHelperClassName(), -14) === 'ElseViewHelper') {
                $arguments = $childNode->getArguments();
                if (isset($arguments['if'])) {
                    if ($arguments['if']->evaluate($this->renderingContext)) {
                        return $childNode->evaluate($this->renderingContext);
                    }
                } else {
                    $elseNode = $childNode;
                }
            }
        }

        return $elseNode instanceof ViewHelperNode ? $elseNode->evaluate($this->renderingContext) : '';
    }

    /**
     * Receives special ViewHelper arguments from compiled templates containing the
     * individual renderChildrenClosures for the condition cases (then/elseif/else)
     * and stores them in class properties for later use.
     */
    public function handleAdditionalArguments(array $arguments): void
    {
        $this->thenClosure = $arguments['__then'] ?? null;
        $this->elseIfClosures = $arguments['__elseIf'] ?? [];
        $this->elseClosure = $arguments['__else'] ?? null;
    }

    /**
     * Optimized version combining default convert() / compile() into one
     * method: The condition VHs dissect children and looks for then, else
     * and elseif nodes, separates them and puts them into special "__"
     * prefixed arguments. The default renderChildrenClosure is not needed
     * and skipped.
     */
    final public function convert(TemplateCompiler $templateCompiler): array
    {
        $node = $this->viewHelperNode;

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $argumentInitializationCode = sprintf('%s = [' . chr(10), $argumentsVariableName);

        $accumulatedArgumentInitializationCode = '';
        $arguments = $node->getArguments();
        $argumentDefinitions = $node->getArgumentDefinitions();
        foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
            if (!array_key_exists($argumentName, $arguments)) {
                // Argument *not* given to VH, use default value
                $defaultValue = $argumentDefinition->getDefaultValue();
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    is_array($defaultValue) && empty($defaultValue) ? '[]' : var_export($defaultValue, true),
                );
            } elseif ($arguments[$argumentName] instanceof NodeInterface) {
                // Argument *is* given to VH and is a node, resolve
                $converted = $arguments[$argumentName]->convert($templateCompiler);
                $accumulatedArgumentInitializationCode .= $converted['initialization'];
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $converted['execution'],
                );
            } else {
                // Argument *is* given to VH and is a simple type.
                // @todo: Why is this not a node object as well? See f:if inline syntax tests.
                $argumentInitializationCode .= sprintf(
                    '\'%s\' => %s,' . chr(10),
                    $argumentName,
                    $arguments[$argumentName],
                );
            }
        }

        $thenChildEncountered = false;
        $elseChildEncountered = false;
        $elseIfCounter = 0;
        $elseIfCode = '\'__elseIf\' => [' . chr(10);
        foreach ($node->getChildNodes() as $childNode) {
            if ($childNode instanceof ViewHelperNode) {
                $viewHelperClassName = $childNode->getViewHelperClassName();
                if (!$thenChildEncountered && str_ends_with($viewHelperClassName, 'ThenViewHelper')) {
                    // If there are multiple f:then children, we pick the first one only.
                    // This is in line with the non-compiled behavior.
                    $thenChildEncountered = true;
                    $argumentInitializationCode .= sprintf(
                        '\'__then\' => %s,' . chr(10),
                        $templateCompiler->wrapChildNodesInClosure($childNode),
                    );
                    continue;
                }
                if (str_ends_with($viewHelperClassName, 'ElseViewHelper')) {
                    if (isset($childNode->getArguments()['if'])) {
                        // This "f:else" has the "if" argument, indicating this is a secondary (elseif) condition.
                        // Compile a closure which will evaluate the condition.
                        $elseIfCode .= sprintf(
                            '    %s => [' . chr(10) .
                            '        \'condition\' => %s,' . chr(10) .
                            '        \'body\' => %s' . chr(10) .
                            '    ],' . chr(10),
                            $elseIfCounter,
                            $templateCompiler->wrapViewHelperNodeArgumentEvaluationInClosure($childNode, 'if'),
                            $templateCompiler->wrapChildNodesInClosure($childNode),
                        );
                        $elseIfCounter++;
                        continue;
                    }
                    if (!$elseChildEncountered) {
                        // If there are multiple f:else children, we pick the first one only.
                        // This is in line with the non-compiled behavior.
                        $elseChildEncountered = true;
                        $argumentInitializationCode .= sprintf(
                            '\'__else\' => %s,' . chr(10),
                            $templateCompiler->wrapChildNodesInClosure($childNode),
                        );
                    }
                }
            }
        }
        if (!$thenChildEncountered && $elseIfCounter === 0 && !$elseChildEncountered && !isset($node->getArguments()['then'])) {
            // If there is no then argument, and there are neither "f:then", "f:else" nor "f:else if" children,
            // then the entire body is considered the "then" child.
            $argumentInitializationCode .= sprintf(
                '\'__then\' => %s,' . chr(10),
                $templateCompiler->wrapChildNodesInClosure($node),
            );
        }

        if ($elseIfCounter > 0) {
            $elseIfCode .= '],' . chr(10);
            $argumentInitializationCode .= $elseIfCode;
        }
        $argumentInitializationCode .= '];' . chr(10);

        return [
            'initialization' => '// Rendering ViewHelper ' . $node->getViewHelperClassName() . chr(10) .
                $accumulatedArgumentInitializationCode . chr(10) .
                $argumentInitializationCode,
            'execution' => sprintf(
                '$renderingContext->getViewHelperInvoker()->invoke(%s::class, %s, $renderingContext)' . chr(10),
                get_class($this),
                $argumentsVariableName,
            ),
        ];
    }
}
