<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

interface ViewHelperNodeInitializedEventInterface
{
    /**
     * Event method that is called after the ViewHelper node has been initialized
     * during template parsing. This can be used by ViewHelpers to alter or
     * append information to the PHP representation of the template. Note that
     * additional changes in the TemplateCompiler might be necessary to also
     * affect cached templates, which is why the utility for third-party ViewHelpers
     * is currently limited.
     *
     * This event aims to replace the previous postParseEvent(), which was never part
     * of this interface. The previous event received the parsing state's variable
     * container as its third argument instead of the whole parsing state, which was
     * limiting its utility. This has been corrected with the new implementation.
     *
     * @param array<string, NodeInterface> $arguments Unevaluated ViewHelper arguments
     */
    public static function nodeInitializedEvent(ViewHelperNode $node, array $arguments, ParsingState $parsingState): void;
}
