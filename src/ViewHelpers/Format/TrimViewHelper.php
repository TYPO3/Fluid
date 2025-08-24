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
 * This ViewHelper strips whitespace (or other characters) from the beginning and end of a string.
 *
 * Possible sides are:
 *
 * ``both`` (default)
 *   Strip whitespace (or other characters) from the beginning and end of a string
 *
 * ``left`` or ``start``
 *   Strip whitespace (or other characters) from the beginning of a string
 *
 * ``right`` or ``end``
 *   Strip whitespace (or other characters) from the end of a string
 *
 *
 * Examples
 * ========
 *
 * Defaults
 * --------
 * ::
 *
 *    #<f:format.trim>   String to be trimmed.   </f:format.trim>#
 *
 * .. code-block:: text
 *
 *    #String to be trimmed.#
 *
 *
 * Trim only one side
 * ------------------
 *
 * ::
 *
 *    #<f:format.trim side="right">   String to be trimmed.   </f:format.trim>#
 *
 * .. code-block:: text
 *
 *    #   String to be trimmed.#
 *
 *
 * Trim special characters
 * -----------------------
 *
 * ::
 *
 *    #<f:format.trim characters=" St.">   String to be trimmed.   </f:format.trim>#
 *
 * .. code-block:: text
 *
 *    #ring to be trimmed#
 *
 *
 * Inline usage
 * -----------------------
 *
 * ::
 *
 *    #{f:format.trim(value: my_variable)}#
 *    #{my_variable -> f:format.trim()}#
 */
final class TrimViewHelper extends AbstractViewHelper
{
    private const SIDE_BOTH = 'both';
    private const SIDE_LEFT = 'left';
    private const SIDE_START = 'start';
    private const SIDE_RIGHT = 'right';
    private const SIDE_END = 'end';

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected ?bool $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The string value to be trimmed. If not given, the evaluated child nodes will be used.');
        $this->registerArgument('characters', 'string', 'Optionally, the stripped characters can also be specified using the characters parameter. Simply list all characters that you want to be stripped. With .. you can specify a range of characters.');
        $this->registerArgument('side', 'string', 'The side to apply, must be one of this\' CASE_* constants. Defaults to both application.', false, self::SIDE_BOTH);
    }

    /**
     * @return string the trimmed value
     */
    public function render(): string
    {
        $value = $this->arguments['value'];
        $characters = $this->arguments['characters'];
        $side = $this->arguments['side'];
        if ($value === null) {
            $value = (string)$this->renderChildren();
        } else {
            $value = (string)$value;
        }
        if ($characters === null) {
            $characters = " \t\n\r\0\x0B";
        }
        return match ($side) {
            self::SIDE_BOTH => trim($value, $characters),
            self::SIDE_LEFT, self::SIDE_START => ltrim($value, $characters),
            self::SIDE_RIGHT, self::SIDE_END => rtrim($value, $characters),
            default => throw new Exception(
                'The side "' . $side . '" supplied to Fluid\'s format.trim ViewHelper is not supported.',
                1669191560,
            ),
        };
    }
}
