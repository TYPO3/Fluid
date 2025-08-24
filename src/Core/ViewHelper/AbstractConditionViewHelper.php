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
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper
{
    protected bool $escapeOutput = false;

    private ?\Closure $thenClosure = null;
    private ?\Closure $elseClosure = null;

    /**
     * @var array<array{'condition': \Closure, 'body': \Closure}>
     */
    private array $elseIfClosures = [];

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.', false, null, true);
        $this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.', false, null, true);
    }

    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     *
     * @api
     */
    public function render(): mixed
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
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
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
    protected function renderThenChild(): mixed
    {
        // In cached templates, a closure is defined if any variant of "then" has been specified
        if ($this->thenClosure !== null) {
            return ($this->thenClosure)();
        }

        // The following code can only be evaluated for uncached templates where the node structure
        // is still available. If it's not, it has already been executed during compilation and we can
        // assume that the condition wasn't met
        if (!$this->viewHelperNode instanceof ViewHelperNode) {
            return null;
        }

        // node arguments are used because the ViewHelper arguments are already merged with default
        // values, which makes it impossible to differentiate between "argument is not defined" and
        // "argument returns null"
        $nodeArguments = $this->viewHelperNode->getArguments();

        // Prefer "then" ViewHelper argument if present
        if (array_key_exists('then', $nodeArguments)) {
            return $this->arguments['then'];
        }

        // Search for f:then ViewHelper and identify possible f:else to decide how the tag's children should
        // be interpreted afterwards
        $elseViewHelperEncountered = false;
        foreach ($this->viewHelperNode->getChildNodes() as $childNode) {
            if ($childNode instanceof ViewHelperNode
                && str_ends_with($childNode->getViewHelperClassName(), 'ThenViewHelper')) {
                $data = $childNode->evaluate($this->renderingContext);
                return $data;
            }
            if ($childNode instanceof ViewHelperNode
                && str_ends_with($childNode->getViewHelperClassName(), 'ElseViewHelper')) {
                $elseViewHelperEncountered = true;
            }
        }

        // If there's a f:else viewhelper, but no matching f:then, the ViewHelper should return "null" as default
        if ($elseViewHelperEncountered) {
            return null;
        }

        // If there's no f:then or f:else, the direct children of the ViewHelper are used as f:then if present
        if ($this->viewHelperNode->getChildNodes() !== []) {
            return $this->renderChildren();
        }

        // If there were no children present, but an else handling is specified as ViewHelper argument,
        // the Viewhelper again should return a "null" as default. If no then/else handling is present at all,
        // the ViewHelper should return the verdict as boolean
        return array_key_exists('else', $nodeArguments) ? null : true;
    }

    /**
     * Returns value of "else" attribute.
     * If else attribute is not set, iterates through child nodes and renders ElseViewHelper.
     * If else attribute is not set and no ElseViewHelper is found, null will be returned.
     *
     * @return mixed rendered ElseViewHelper or null if no ThenViewHelper was found
     * @api
     */
    protected function renderElseChild(): mixed
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

        // Closure might be present if ViewHelper is called from a cached template
        if ($this->elseClosure !== null) {
            return ($this->elseClosure)();
        }

        // The following code can only be evaluated for uncached templates where the node structure
        // is still available. If it's not, it has already been executed during compilation and we can
        // assume that the condition wasn't met
        if (!$this->viewHelperNode instanceof ViewHelperNode) {
            return null;
        }

        /** @var ViewHelperNode|null $elseNode */
        $elseNode = null;
        foreach ($this->viewHelperNode->getChildNodes() as $childNode) {
            if ($childNode instanceof ViewHelperNode
                && str_ends_with($childNode->getViewHelperClassName(), 'ElseViewHelper')) {
                $arguments = $childNode->getArguments();
                if (isset($arguments['if'])) {
                    if ($arguments['if']->evaluate($this->renderingContext)) {
                        return $childNode->evaluate($this->renderingContext);
                    }
                } elseif ($elseNode === null) {
                    $elseNode = $childNode;
                }
            }
        }

        // node arguments are used because the ViewHelper arguments are already merged with default
        // values, which makes it impossible to differentiate between "argument is not defined" and
        // "argument returns null"
        $nodeArguments = $this->viewHelperNode->getArguments();

        // If no else-if matches here and an else argument exists, this is prefered over
        // a possible f:else ViewHelper. See above for the same implementation for cached templates
        if (array_key_exists('else', $nodeArguments)) {
            return $this->arguments['else'];
        }

        // If a f:else node exists, evaluate its content
        if ($elseNode instanceof ViewHelperNode) {
            return $elseNode->evaluate($this->renderingContext);
        }

        // If only the condition is specified, but no then/else handling, the whole ViewHelper should
        // return the verdict as boolean. Most code paths have already been eliminated until this point,
        // so the existence of a valid then decides if the boolean should be returned
        if (!array_key_exists('then', $nodeArguments) && $this->viewHelperNode->getChildNodes() === []) {
            return false;
        }

        // If some kind of then handling has been specified, the ViewHelper always returns "null" as default
        return null;
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
        $nodeArguments = $node->getArguments();
        $argumentsCode = [];

        // Convert then/else arguments to closures to simplify handling for cached templates
        // This also allows to differentiate between undefined and null states for cached templates
        foreach (['then', 'else'] as $argumentName) {
            if (array_key_exists($argumentName, $nodeArguments)) {
                $argumentsCode['__' . $argumentName]
                    = $nodeArguments[$argumentName] instanceof NodeInterface
                    ? $templateCompiler->wrapViewHelperNodeArgumentEvaluationInClosure($node, $argumentName)
                    : 'function () { return ' . $nodeArguments[$argumentName] . ';}';
            }
        }

        $elseChildEncountered = false;
        foreach ($node->getChildNodes() as $childNode) {
            if (!$childNode instanceof ViewHelperNode) {
                continue;
            }
            $viewHelperClassName = $childNode->getViewHelperClassName();
            if (!isset($argumentsCode['__then']) && str_ends_with($viewHelperClassName, 'ThenViewHelper')) {
                // If there are multiple f:then children, we pick the first one only.
                // This is in line with the non-compiled behavior.
                $argumentsCode['__then'] =  $templateCompiler->wrapChildNodesInClosure($childNode);
            } elseif (str_ends_with($viewHelperClassName, 'ElseViewHelper')) {
                $elseChildEncountered = true;
                if (isset($childNode->getArguments()['if'])) {
                    // This "f:else" has the "if" argument, indicating this is a secondary (elseif) condition.
                    // Compile a closure which will evaluate the condition.
                    $argumentsCode['__elseIf'] ??= [];
                    $argumentsCode['__elseIf'][] = [
                        'condition' => $templateCompiler->wrapViewHelperNodeArgumentEvaluationInClosure($childNode, 'if'),
                        'body' => $templateCompiler->wrapChildNodesInClosure($childNode),
                    ];
                } elseif (!isset($argumentsCode['__else'])) {
                    // If there are multiple f:else children, we pick the first one only.
                    // This is in line with the non-compiled behavior.
                    $argumentsCode['__else'] = $templateCompiler->wrapChildNodesInClosure($childNode);
                }
            }
        }

        $accumulatedArgumentInitializationCode = '';
        foreach ($node->getArgumentDefinitions() as $argumentName => $argumentDefinition) {
            // then/else has already been dealt with above
            if ($argumentName === 'then' || $argumentName === 'else') {
                continue;
            }
            if (!array_key_exists($argumentName, $nodeArguments)) {
                // Argument *not* given to VH, use default value
                $defaultValue = $argumentDefinition->getDefaultValue();
                $argumentsCode[$argumentName] = is_array($defaultValue) && empty($defaultValue) ? '[]' : var_export($defaultValue, true);
            } elseif ($nodeArguments[$argumentName] instanceof NodeInterface) {
                // Argument *is* given to VH and is a node, resolve
                $converted = $nodeArguments[$argumentName]->convert($templateCompiler);
                $accumulatedArgumentInitializationCode .= $converted['initialization'];
                $argumentsCode[$argumentName] = $converted['execution'];
            } else {
                // Argument *is* given to VH and is a simple type.
                // @todo: Why is this not a node object as well? See f:if inline syntax tests.
                $argumentsCode[$argumentName] = $nodeArguments[$argumentName];
            }
        }

        // If there is no then argument, and there are neither "f:then", "f:else" nor "f:else if" children,
        // then the entire body is considered the "then" child if it is specified.
        if (!isset($argumentsCode['__then']) && !$elseChildEncountered && $node->getChildNodes() !== []) {
            $argumentsCode['__then'] = $templateCompiler->wrapChildNodesInClosure($node);
        }

        // If the ViewHelper has no then or else specified in any supported way, the verdict will be
        // returned directly. This allows usage of custom condition-based ViewHelpers in f:if, like
        // <f:if condition="{my:conditionBasedViewHelper()} || somethingElse">
        if (!isset($argumentsCode['__then']) && !isset($argumentsCode['__elseIf']) && !isset($argumentsCode['__else'])) {
            $argumentsCode['__then'] = 'function () { return true;}';
            $argumentsCode['__else'] = 'function () { return false;}';
        }

        $argumentsVariableName = $templateCompiler->variableName('arguments');
        $argumentInitializationCode = sprintf(
            '%s = %s;',
            $argumentsVariableName,
            $templateCompiler->generateViewHelperArgumentsCode($argumentsCode),
        );

        return [
            'initialization' => '// Rendering ViewHelper ' . $node->getViewHelperClassName() . chr(10)
                . $accumulatedArgumentInitializationCode . chr(10)
                . $argumentInitializationCode . chr(10),
            'execution' => sprintf(
                '$renderingContext->getViewHelperInvoker()->invoke(%s::class, %s, $renderingContext)' . chr(10),
                get_class($this),
                $argumentsVariableName,
            ),
        ];
    }
}
