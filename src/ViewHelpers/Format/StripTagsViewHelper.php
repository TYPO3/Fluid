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
 * Removes tags from the given string (applying PHPs :php:`strip_tags()` function)
 * See https://www.php.net/manual/function.strip-tags.php.
 *
 * Examples
 * ========
 *
 * Default notation
 * ----------------
 *
 * ::
 *
 *    <f:format.stripTags>Some Text with <b>Tags</b> and an &Uuml;mlaut.</f:format.stripTags>
 *
 * Some Text with Tags and an &Uuml;mlaut. :php:`strip_tags()` applied.
 *
 * .. note::
 *    Encoded entities are not decoded.
 *
 * Default notation with allowedTags
 * ---------------------------------
 *
 * ::
 *
 *    <f:format.stripTags allowedTags="<p><span><div><script>">
 *        <p>paragraph</p><span>span</span><div>divider</div><iframe>iframe</iframe><script>script</script>
 *    </f:format.stripTags>
 *
 * Output::
 *
 *    <p>paragraph</p><span>span</span><div>divider</div>iframe<script>script</script>
 *
 * Inline notation
 * ---------------
 *
 * ::
 *
 *    {text -> f:format.stripTags()}
 *
 * Text without tags :php:`strip_tags()` applied.
 *
 * Inline notation with allowedTags
 * --------------------------------
 *
 * ::
 *
 *    {text -> f:format.stripTags(allowedTags: "<p><span><div><script>")}
 *
 * Text with p, span, div and script Tags inside, all other tags are removed.
 */
final class StripTagsViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * No output escaping as some tags may be allowed
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'string to format');
        $this->registerArgument('allowedTags', 'string', 'Optional string of allowed tags as required by PHPs strip_tags() function');
    }

    /**
     * To ensure all tags are removed, child node's output must not be escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Applies strip_tags() on the specified value if it's string-able.
     *
     * @see https://www.php.net/manual/function.strip-tags.php
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $value = $renderChildrenClosure();
        $allowedTags = $arguments['allowedTags'];

        if (is_array($value)) {
            throw new \InvalidArgumentException('Specified array cannot be converted to string.', 1700819707);
        }
        if (is_object($value) && !($value instanceof Stringable)) {
            throw new \InvalidArgumentException('Specified object cannot be converted to string.', 1700819706);
        }
        return strip_tags((string)$value, $allowedTags);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function resolveContentArgumentName(): string
    {
        return 'value';
    }
}
