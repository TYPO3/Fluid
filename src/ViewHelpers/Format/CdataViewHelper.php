<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Outputs an argument/value without any escaping and wraps it with CDATA tags.
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
 *     <f:format.cdata>{string}</f:format.cdata>
 *
 * Output::
 *
 *     <![CDATA[(Content of {string} without any conversion/escaping)]]>
 *
 * Value attribute
 * ---------------
 *
 * ::
 *
 *     <f:format.cdata value="{string}" />
 *
 * Output::
 *
 *     <![CDATA[(Content of {string} without any conversion/escaping)]]>
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *     {string -> f:format.cdata()}
 *
 * Output::
 *
 *     <![CDATA[(Content of {string} without any conversion/escaping)]]>
 *
 * @api
 */
class CdataViewHelper extends AbstractViewHelper
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
        $this->registerArgument('value', 'mixed', 'The value to output');
    }

    public function render(): string
    {
        return sprintf('<![CDATA[%s]]>', $this->renderChildren());
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
