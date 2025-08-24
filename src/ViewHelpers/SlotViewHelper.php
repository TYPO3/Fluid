<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperNodeInitializedEventInterface;

/**
 * ``f:slot`` allows a template that is called as a component to access and render
 * the child content of the calling component tag. This makes nesting of components
 * possible.
 *
 * Most importantly, the ``f:slot`` ViewHelper makes sure that the right level of
 * HTML escaping happens automatically, in line with the escaping in other parts of
 * Fluid: If HTML is used directly, it is not escaped. However, if a variable is
 * used within the child content that contains a HTML string, that HTML is escaped
 * because it might be from an unknown source.
 *
 * In combination with the :ref:`<f:fragment> ViewHelper <typo3fluid-fluid-fragment>`,
 * multiple slots can be used in one component.
 *
 * If a slot is defined, this ViewHelper will always attempt to return a string,
 * regardless of the original type of the content. If a slot is not defined, the
 * ViewHelper will return ``null``.
 *
 * Basic Example
 * =============
 *
 * If the following template ``Text.html``:
 *
 * ..  code-block:: xml
 *     :emphasize-lines: 6
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
 * .. code-block:: xml
 *
 *    <my:text title="My title">
 *        <p>My first paragraph</p>
 *        <p>My second paragraph</p>
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
 * Escaping Example
 * ================
 *
 * If the same component is called like this:
 *
 * .. code-block:: xml
 *
 *    <f:variable name="htmlString">
 *        <p>My first paragraph</p>
 *        <p>My second paragraph</p>
 *    </f:variable>
 *    <my:text title="My title">{htmlString}</my:text>
 *
 * it would result in escaped HTML:
 *
 * .. code-block:: xml
 *
 *    <div class="textComponent">
 *        <h2>My title</h2>
 *        <div class="textComponent__content">
 *            &lt;p&gt;My first paragraph&lt;/p&gt;
 *            &lt;p&gt;My second paragraph&lt;/p&gt;
 *        </div>
 *    </div>
 *
 * If you want to avoid escaping in this use case, you need to use ``f:format.raw`` on
 * the variable when it's passed to the component. Please be aware that depending on
 * the source of the input, this might have security implications!
 *
 * Component Nesting Example
 * =========================
 *
 * Nesting of multiple components is possible. The following template ``Paragraphs.html``:
 *
 * .. code-block:: xml
 *
 *    <p>My first paragraph</p>
 *    <p>My second paragraph</p>
 *
 * can be called as a component and nested into the text component described above:
 *
 * .. code-block:: xml
 *
 *    <my:text title="My title">
 *        <my:paragraphs />
 *    </my:text>
 *
 * which would lead to unescaped output, since components are always expected to return HTML:
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
 * ..  code-block:: xml
 *     :emphasize-lines: 6,9
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
 * .. code-block:: xml
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
final class SlotViewHelper extends AbstractViewHelper implements ViewHelperNodeInitializedEventInterface
{
    public const DEFAULT_SLOT = 'default';

    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of the slot, can be omitted for default slot', false, self::DEFAULT_SLOT);
    }

    public function render(): ?string
    {
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $slot = $variableContainer->get(self::class, $this->arguments['name']);
        return is_callable($slot) ? (string)$slot() : null;
    }

    public static function nodeInitializedEvent(ViewHelperNode $node, array $arguments, ParsingState $parsingState): void
    {
        // Collect available slots of current template in ParsingState
        // This allows to extract template metadata without triggering a
        // full rendering of the template
        $slotName = isset($arguments['name']) && $arguments['name'] instanceof NodeInterface
            ? (string)$arguments['name']->evaluate(new RenderingContext())
            : self::DEFAULT_SLOT;
        if (!in_array($slotName, $parsingState->getAvailableSlots())) {
            $parsingState->setAvailableSlots([...$parsingState->getAvailableSlots(), $slotName]);
        }
    }
}
