<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

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

    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'mixed', 'The value to output');
    }

    /**
     * @param array $arguments
     * @param callable $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return sprintf('<![CDATA[%s]]>', $renderChildrenClosure());
    }
}
