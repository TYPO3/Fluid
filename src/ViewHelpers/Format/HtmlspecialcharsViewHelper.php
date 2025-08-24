<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Applies PHP ``htmlspecialchars()`` escaping to a value.
 *
 * See http://www.php.net/manual/function.htmlspecialchars.php
 *
 * Examples
 * ========
 *
 * Default notation
 * ----------------
 *
 * ::
 *
 *     <f:format.htmlspecialchars>{text}</f:format.htmlspecialchars>
 *
 * Output::
 *
 *     Text with & " ' < > * replaced by HTML entities (htmlspecialchars applied).
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *     {text -> f:format.htmlspecialchars(encoding: 'ISO-8859-1')}
 *
 * Output::
 *
 *     Text with & " ' < > * replaced by HTML entities (htmlspecialchars applied).
 *
 * @api
 */
class HtmlspecialcharsViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    /**
     * Disable the output escaping interceptor so that the value is not htmlspecialchar'd twice
     *
     * @var bool
     */
    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Value to format');
        $this->registerArgument('keepQuotes', 'boolean', 'If true quotes will not be replaced (ENT_NOQUOTES)', false, false);
        $this->registerArgument('encoding', 'string', 'Encoding', false, 'UTF-8');
        $this->registerArgument('doubleEncode', 'boolean', 'If false, html entities will not be encoded', false, true);
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs htmlspecialchars() function.
     *
     * @return mixed the altered string. If a non-string is provided, the value is returned unchanged
     * @see http://www.php.net/manual/function.htmlspecialchars.php
     * @api
     */
    public function render(): mixed
    {
        $value = $this->arguments['value'];
        $keepQuotes = $this->arguments['keepQuotes'];
        $encoding = $this->arguments['encoding'];
        $doubleEncode = $this->arguments['doubleEncode'];
        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (!is_string($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            return $value;
        }
        $flags = $keepQuotes ? ENT_NOQUOTES : ENT_QUOTES;

        return htmlspecialchars($value, $flags, $encoding, $doubleEncode);
    }
}
