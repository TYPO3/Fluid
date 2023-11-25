<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use Stringable;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Encodes the given string according to http://www.faqs.org/rfcs/rfc3986.html
 * Applying PHPs :php:`rawurlencode()` function.
 * See https://www.php.net/manual/function.rawurlencode.php.
 *
 * .. note::
 *    The output is not escaped. You may have to ensure proper escaping on your own.
 *
 * Examples
 * ========
 *
 * Default notation
 * ----------------
 *
 * ::
 *
 *    <f:format.urlencode>foo @+%/</f:format.urlencode>
 *
 * ``foo%20%40%2B%25%2F`` :php:`rawurlencode()` applied.
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *    {text -> f:format.urlencode()}
 *
 * Url encoded text :php:`rawurlencode()` applied.
 */
final class UrlencodeViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'string to format');
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs rawurlencode() function.
     *
     * @see https://www.php.net/manual/function.rawurlencode.php
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $value = $renderChildrenClosure();
        if (is_array($value)) {
            throw new \InvalidArgumentException('Specified array cannot be converted to string.', 1700821579);
        }
        if (is_object($value) && !($value instanceof Stringable)) {
            throw new \InvalidArgumentException('Specified object cannot be converted to string.', 1700821578);
        }
        return rawurlencode((string)$value);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function resolveContentArgumentName(): string
    {
        return 'value';
    }
}
