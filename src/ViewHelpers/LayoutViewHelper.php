<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperNodeInitializedEventInterface;

/**
 * With this ViewHelper, you can select a layout to be used for the current template.
 *
 * ..  deprecated:: 4.4
 *     Prevously, it was possible to set the layout of a template with the special
 *     variable `layoutName`. This will no longer work with Fluid 5.
 *
 * Examples
 * ========
 *
 * ::
 *
 *     <f:layout name="main" />
 *
 * Output::
 *
 *     (no output)
 *
 * @api
 */
class LayoutViewHelper extends AbstractViewHelper implements ViewHelperNodeInitializedEventInterface
{
    /**
     * Initialize arguments
     *
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of layout to use. If none given, "Default" is used.');
    }

    public function render()
    {
        return '';
    }

    /**
     * This VH does not ever output anything as such: Layouts are
     * handled differently in the compiler / parser and the f:render
     * VH invokes section body execution.
     * We optimize compilation to always return an empty here.
     */
    final public function convert(TemplateCompiler $templateCompiler): array
    {
        return [
            'initialization' => '',
            'execution' => '\'\'',
        ];
    }

    /**
     * Set the defined layout name (which can include variables) to the ParsingState,
     * to be used both for compilation and uncached rendering
     *
     * @param array<string, NodeInterface> $arguments Unevaluated ViewHelper arguments
     */
    public static function nodeInitializedEvent(ViewHelperNode $node, array $arguments, ParsingState $parsingState): void
    {
        if (isset($arguments['name'])) {
            $layoutNameNode = $arguments['name'];
        } else {
            $layoutNameNode = 'Default';
        }
        $parsingState->setLayoutName($layoutNameNode);
    }
}
