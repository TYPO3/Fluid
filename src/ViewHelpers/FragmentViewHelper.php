<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Component\ComponentAdapter;
use TYPO3Fluid\Fluid\Core\Parser\Exception as ParserException;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperNodeInitializedEventInterface;

/**
 * ``f:fragment`` is the counterpart of the :ref:`<f:slot> ViewHelper <typo3fluid-fluid-slot>`.
 * It allows to provide multiple HTML fragments to a component, which defines
 * the matching named slots. ``f:fragment`` is used directly inside a component
 * tag, nesting into other ViewHelpers is not possible.
 *
 * Default Fragment Example
 * ========================
 *
 * If the following template ``Text.html``:
 *
 * .. code-block:: xml
 *
 *    <f:argument name="title" type="string" />
 *
 *    <div class="textComponent">
 *        <h2>{title}</h2>
 *        <div class="textComponent__content">
 *            <f:slot />
 *        </div>
 *    </div>
 *
 * is rendered with the following component call:
 *
 * ..  code-block:: xml
 *     :emphasize-lines: 2,5
 *
 *    <my:text title="My title">
 *        <f:fragment>
 *            <p>My first paragraph</p>
 *            <p>My second paragraph</p>
 *        </f:fragment>
 *    </my:text>
 *
 * it will result in the following output:
 *
 * .. code-block:: xml
 *
 *    <div class="textComponent">
 *        <h2>My title</h2>
 *        <div class="textComponent__content">
 *            <p>My first paragraph</p>
 *            <p>My second paragraph</p>
 *        </div>
 *    </div>
 *
 * Multiple Named Slots
 * ====================
 *
 * If the following template ``TextMedia.html``:
 *
 * .. code-block:: xml
 *
 *    <f:argument name="title" type="string" />
 *
 *    <div class="textMediaComponent">
 *        <h2>{title}</h2>
 *        <div class="textMediaComponent__media">
 *            <f:slot name="media" />
 *        </div>
 *        <div class="textMediaComponent__content">
 *            <f:slot name="content" />
 *        </div>
 *    </div>
 *
 * is rendered with the following component call:
 *
 * ..  code-block:: xml
 *     :emphasize-lines: 2,4,5,8
 *
 *    <my:textMedia title="My title">
 *        <f:fragment name="media">
 *            <img src="path/to/image.jpg" alt="..." />
 *        </f:fragment>
 *        <f:fragment name="content">
 *            <p>My first paragraph</p>
 *            <p>My second paragraph</p>
 *        </f:fragment>
 *    </my:textMedia>
 *
 * it will result in the following output:
 *
 * .. code-block:: xml
 *
 *    <div class="textMediaComponent">
 *        <h2>My title</h2>
 *        <div class="textMediaComponent__media">
 *            <img src="path/to/image.jpg" alt="..." />
 *        </div>
 *        <div class="textMediaComponent__content">
 *            <p>My first paragraph</p>
 *            <p>My second paragraph</p>
 *        </div>
 *    </div>
 *
 * @api
 */
final class FragmentViewHelper extends AbstractViewHelper implements ViewHelperNodeInitializedEventInterface
{
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the slot that should be filled', false, SlotViewHelper::DEFAULT_SLOT);
    }

    public function render(): string
    {
        return '';
    }

    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler): string
    {
        return '\'\'';
    }

    public static function nodeInitializedEvent(ViewHelperNode $viewHelperNode, array $arguments, ParsingState $parsingState): void
    {
        // For now, we limit the use fragments to the component context
        $parentNode = $parsingState->getNodeFromStack();
        if (!$parentNode instanceof ViewHelperNode || !$parentNode->getUninitializedViewHelper() instanceof ComponentAdapter) {
            throw new ParserException('Fragments can only be used as a direct descendent of components.', 1750865702);
        }
    }
}
