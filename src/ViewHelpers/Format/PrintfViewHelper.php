<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A ViewHelper for formatting values with printf. Either supply an array for
 * the arguments or a single value.
 *
 * See http://www.php.net/manual/en/function.sprintf.php
 *
 * Examples
 * ========
 *
 * Scientific notation
 * -------------------
 *
 * ::
 *
 *     <f:format.printf arguments="{number: 362525200}">%.3e</f:format.printf>
 *
 * Output::
 *
 *     3.625e+8
 *
 * Argument swapping
 * -----------------
 *
 * ::
 *
 *     <f:format.printf arguments="{0: 3, 1: 'Kasper'}">%2$s is great, TYPO%1$d too. Yes, TYPO%1$d is great and so is %2$s!</f:format.printf>
 *
 * Output::
 *
 *     Kasper is great, TYPO3 too. Yes, TYPO3 is great and so is Kasper!
 *
 * Single argument
 * ---------------
 *
 * ::
 *
 *     <f:format.printf arguments="{1: 'TYPO3'}">We love %s</f:format.printf>
 *
 *
 * Output::
 *
 *     We love TYPO3
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *     {someText -> f:format.printf(arguments: {1: 'TYPO3'})}
 *
 *
 * Output::
 *
 *     We love TYPO3
 *
 * @api
 */
class PrintfViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'String to format');
        $this->registerArgument('arguments', 'array', 'The arguments for vsprintf', false, []);
    }

    /**
     * Applies vsprintf() on the specified value.
     * @return string
     */
    public function render()
    {
        return vsprintf($this->renderChildren(), $this->arguments['arguments']);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
