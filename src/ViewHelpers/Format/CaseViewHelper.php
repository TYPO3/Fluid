<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Modifies the case of an input string to upper- or lowercase or capitalization.
 * The default transformation will be uppercase as in `mb_convert_case`_.
 *
 * Possible modes are:
 *
 * ``lower``
 *   Transforms the input string to lowercase
 *   Example: "Hello World" -> "hello world"
 *
 * ``upper``
 *   Transforms the input string to uppercase
 *   Example: "Hello World" -> "HELLO WORLD"
 *
 * ``capital``
 *   Transforms the first character of the input string to uppercase
 *   Example: "hello world" -> "Hello world"
 *
 * ``uncapital``
 *   Transforms the input string to its first letter lower-cased
 *   Example: "Hello World" -> "hello World"
 *
 * ``capitalWords``
 *   Transforms the input string to capitalize each word
 *   Example: "hello world" -> "Hello World"
 *
 * Note that the behavior will be the same as in the appropriate PHP function `mb_convert_case`_;
 * especially regarding locale and multibyte behavior.
 *
 * .. _mb_convert_case: https://www.php.net/manual/function.mb-convert-case.php
 *
 * Examples
 * ========
 *
 * Default
 * -------
 *
 * ::
 *
 *    <f:format.case>Some Text with miXed case</f:format.case>
 *
 * Output::
 *
 *    SOME TEXT WITH MIXED CASE
 *
 * Example with given mode
 * -----------------------
 *
 * ::
 *
 *    <f:format.case mode="capital">someString</f:format.case>
 *
 * Output::
 *
 *    SomeString
 */
final class CaseViewHelper extends AbstractViewHelper
{
    /**
     * Directs the input string being converted to "lowercase"
     */
    private const CASE_LOWER = 'lower';

    /**
     * Directs the input string being converted to "UPPERCASE"
     */
    private const CASE_UPPER = 'upper';

    /**
     * Directs the input string being converted to "Capital case"
     */
    private const CASE_CAPITAL = 'capital';

    /**
     * Directs the input string being converted to "unCapital case"
     */
    private const CASE_UNCAPITAL = 'uncapital';

    /**
     * Directs the input string being converted to "Capital Case For Each Word"
     */
    private const CASE_CAPITAL_WORDS = 'capitalWords';

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The input value. If not given, the evaluated child nodes will be used.');
        $this->registerArgument('mode', 'string', 'The case to apply, must be one of this\' CASE_* constants. Defaults to uppercase application.', false, self::CASE_UPPER);
    }

    /**
     * Changes the case of the input string
     * @throws Exception
     */
    public function render(): string
    {
        $value = $this->arguments['value'];
        $mode = $this->arguments['mode'];
        if ($value === null) {
            $value = (string)$this->renderChildren();
        }
        switch ($mode) {
            case self::CASE_LOWER:
                $output = mb_strtolower($value, 'utf-8');
                break;
            case self::CASE_UPPER:
                $output = mb_strtoupper($value, 'utf-8');
                break;
            case self::CASE_CAPITAL:
                $firstChar = mb_substr($value, 0, 1, 'utf-8');
                $firstChar = mb_strtoupper($firstChar, 'utf-8');
                $remainder = mb_substr($value, 1, null, 'utf-8');
                $output = $firstChar . $remainder;
                break;
            case self::CASE_UNCAPITAL:
                $firstChar = mb_substr($value, 0, 1, 'utf-8');
                $firstChar = mb_strtolower($firstChar, 'utf-8');
                $remainder = mb_substr($value, 1, null, 'utf-8');
                $output = $firstChar . $remainder;
                break;
            case self::CASE_CAPITAL_WORDS:
                $output = mb_convert_case($value, MB_CASE_TITLE, 'utf-8');
                break;
            default:
                throw new Exception('The case mode "' . $mode . '" supplied to Fluid\'s format.case ViewHelper is not supported.', 1358349150);
        }
        return $output;
    }
}
