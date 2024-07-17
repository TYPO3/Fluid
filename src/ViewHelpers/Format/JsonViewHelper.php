<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Wrapper for PHPs :php:`json_encode` function.
 * See https://www.php.net/manual/function.json-encode.php.
 *
 * Examples
 * ========
 *
 * Encoding a view variable
 * ------------------------
 *
 * ::
 *
 *    {someArray -> f:format.json()}
 *
 * ``["array","values"]``
 * Depending on the value of ``{someArray}``.
 *
 * Associative array
 * -----------------
 *
 * ::
 *
 *    {f:format.json(value: {foo: 'bar', bar: 'baz'})}
 *
 * ``{"foo":"bar","bar":"baz"}``
 *
 * Non associative array with forced object
 * ----------------------------------------
 *
 * ::
 *
 *    {f:format.json(value: {0: 'bar', 1: 'baz'}, forceObject: true)}
 *
 * ``{"0":"bar","1":"baz"}``
 */
final class JsonViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'mixed', 'The incoming data to convert, or null if VH children should be used');
        $this->registerArgument('forceObject', 'bool', 'Outputs an JSON object rather than an array', false, false);
    }

    /**
     * Applies json_encode() on the specified value.
     *
     * Outputs content with its JSON representation. To prevent issues in HTML context, occurrences
     * of greater-than or less-than characters are converted to their hexadecimal representations.
     *
     * If $forceObject is true a JSON object is outputted even if the value is a non-associative array
     * Example: array('foo', 'bar') as input will not be ["foo","bar"] but {"0":"foo","1":"bar"}
     *
     * @see https://www.php.net/manual/function.json-encode.php
     * @return string|false
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $renderChildrenClosure();
        $options = JSON_HEX_TAG;
        if ($arguments['forceObject'] !== false) {
            $options = $options | JSON_FORCE_OBJECT;
        }
        return json_encode($value, $options);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function resolveContentArgumentName(): string
    {
        return 'value';
    }
}
