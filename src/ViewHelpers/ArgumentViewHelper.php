<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperNodeInitializedEventInterface;

/**
 * ``f:argument`` allows to define requirements and type constraints to variables that
 * are provided to templates and partials. This can be very helpful to document how
 * a template or partial is supposed to be used and which input variables are required.
 *
 * These requirements are enforced during rendering of the template or partial:
 * If an argument is defined with this ViewHelper which isn't marked as ``optional``,
 * an exception will be thrown if that variable isn't present during rendering.
 * If a variable doesn't match the specified type and can't be converted automatically,
 * an exception will be thrown as well.
 *
 * Note that ``f:argument`` ViewHelpers must be used at the root level of the
 * template, and can't be nested into other ViewHelpers. Also, the usage of variables
 * in any of its arguments is not possible (e. g. you can't define an argument name
 * by using a variable).
 *
 * Example
 * ========
 *
 * For the following partial:
 *
 * .. code-block:: xml
 *
 *    <f:argument name="title" type="string" />
 *    <f:argument name="tags" type="string[]" optional="{true}" />
 *    <f:argument name="user" type="string" optional="{true}" default="admin" />
 *
 *    Title: {title}<br />
 *    <f:if condition="{tags}">
 *      Tags: {tags -> f:join(separator: ', ')}<br />
 *    </f:if>
 *    User: {user}
 *
 * The following render calls will be successful:
 *
 * .. code-block:: xml
 *
 *    <!-- All arguments supplied -->
 *    <f:render partial="MyPartial" arguments="{title: 'My title', tags: {0: 'tag1', 1: 'tag2'}, user: 'me'}" />
 *    <!-- "user" will fall back to default value -->
 *    <f:render partial="MyPartial" arguments="{title: 'My title', tags: {0: 'tag1', 1: 'tag2'}}" />
 *    <!-- "tags" will be "null", "user" will fall back to default value -->
 *    <f:render partial="MyPartial" arguments="{title: 'My title'}" />
 *
 * The following render calls will result in an exception:
 *
 * .. code-block:: xml
 *
 *    <!-- required "title" has not been supplied -->
 *    <f:render partial="MyPartial" />
 *    <!-- "user" has been supplied as array, not as string -->
 *    <f:render partial="MyPartial" arguments="{title: 'My title', user: {firstName: 'Jane', lastName: 'Doe'}}" />
 *
 * @api
 */
final class ArgumentViewHelper extends AbstractViewHelper implements ViewHelperNodeInitializedEventInterface
{
    /**
     * No need to add escaping nodes since the ViewHelper doesn't output anything
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'name of the template argument', true);
        $this->registerArgument('type', 'string', 'type of the template argument', true);
        $this->registerArgument('description', 'string', 'description of the template argument');
        $this->registerArgument('optional', 'boolean', 'true if the defined argument should be optional', false, false);
        $this->registerArgument('default', 'mixed', 'default value for optional argument');
    }

    public function render(): string
    {
        return '';
    }

    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler): string
    {
        return '\'\'';
    }

    public static function nodeInitializedEvent(ViewHelperNode $node, array $arguments, ParsingState $parsingState): void
    {
        // Create static values of supplied arguments. A new empty rendering context is used here
        // because argument definitions shouldn't be dependent on any variables in the template.
        // Any variables that are used anyway (e. g. in default values) will be interpreted as "null"
        $emptyRenderingContext = new RenderingContext();
        $evaluatedArguments = array_map(
            static fn(NodeInterface $node): mixed => $node->evaluate($emptyRenderingContext),
            $arguments,
        );
        $argumentName = (string)$evaluatedArguments['name'];

        // Make sure that arguments are not nested into other ViewHelpers as this might create confusion
        if ($parsingState->hasNodeTypeInStack(ViewHelperNode::class)) {
            throw new \TYPO3Fluid\Fluid\Core\Parser\Exception(sprintf(
                'Template argument "%s" needs to be defined at the root level of the template, not within a ViewHelper.',
                $argumentName,
            ), 1744908510);
        }

        // Make sure that this argument hasn't already been defined in the template
        $argumentDefinitions = $parsingState->getArgumentDefinitions();
        if (isset($argumentDefinitions[$argumentName])) {
            throw new \TYPO3Fluid\Fluid\Core\Parser\Exception(sprintf(
                'Template argument "%s" has been defined multiple times.',
                $argumentName,
            ), 1744908509);
        }

        // Automatically make the argument definition optional if it has a default value
        $hasDefaultValue = array_key_exists('default', $evaluatedArguments);
        $optional = ($evaluatedArguments['optional'] ?? false) || $hasDefaultValue;

        // Create argument definition to be interpreted later during rendering
        // This will also be written to the cache by the TemplateCompiler
        $argumentDefinitions[$argumentName] = new ArgumentDefinition(
            $argumentName,
            (string)$evaluatedArguments['type'],
            array_key_exists('description', $evaluatedArguments) ? (string)$evaluatedArguments['description'] : '',
            !$optional,
            $hasDefaultValue ? $evaluatedArguments['default'] : null,
        );
        $parsingState->setArgumentDefinitions($argumentDefinitions);
    }
}
