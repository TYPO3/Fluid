<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Outputs an argument/value without any escaping. Is normally used to output
 * an ObjectAccessor which should not be escaped, but output as-is.
 *
 * PAY SPECIAL ATTENTION TO SECURITY HERE (especially Cross Site Scripting),
 * as the output is NOT SANITIZED!
 *
 * Examples
 * ========
 *
 * Child nodes
 * -----------
 *
 * ::
 *
 *     <f:format.raw>{string}</f:format.raw>
 *
 * Output::
 *
 *     (Content of ``{string}`` without any conversion/escaping)
 *
 * Value attribute
 * ---------------
 *
 * ::
 *
 *     <f:format.raw value="{string}" />
 *
 * Output::
 *
 *     (Content of ``{string}`` without any conversion/escaping)
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *     {string -> f:format.raw()}
 *
 * Output::
 *
 *     (Content of ``{string}`` without any conversion/escaping)
 *
 * @api
 */
class RawViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    /**
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The value to output', false, null, false);
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return $this->renderChildren();
    }

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     */
    public function compile($argumentsName, $closureName, &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler): string
    {
        $contentArgumentName = $this->getContentArgumentName();
        return sprintf(
            'isset(%s[\'%s\']) ? %s[\'%s\'] : %s()',
            $argumentsName,
            $contentArgumentName,
            $argumentsName,
            $contentArgumentName,
            $closureName,
        );
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
