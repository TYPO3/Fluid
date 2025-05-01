<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

use Stringable;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
 * Default notation with hrefsInBrackets
 * ---------------------------------
 *
 * ::
 *
 *    <f:format.stripTags hrefsInBrackets="true">
 *        <a href="https://example.com">Link Text</a>
 *    </f:format.stripTags>
 *
 * Output::
 *
 *    "Link Text [https://example.com]"
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
 *
 * Inline notation with hrefsInBrackets
 * --------------------------------
 *
 * ::
 *
 *    {text -> f:format.stripTags(hrefsInBrackets: "true")}
 *
 * Href links in Text in square brackets next to the link text, e.g. "Link Text [https://example.com]".
 *
 */
final class StripTagsViewHelper extends AbstractViewHelper
{
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
        $this->registerArgument(
            'hrefsInBrackets',
            'bool',
            'When enabled, detects <a> tags in the content and appends their href links in square brackets next to the link text, e.g. "Link Text [https://example.com]"'
        );
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
    public function render(): string
    {
        $value = $this->renderChildren();
        $allowedTags = $this->arguments['allowedTags'];
        $hrefsInBrackets = $this->arguments['hrefsInBrackets'];
        if (is_array($value)) {
            throw new \InvalidArgumentException('Specified array cannot be converted to string.', 1700819707);
        }
        if (is_object($value) && !($value instanceof Stringable)) {
            throw new \InvalidArgumentException('Specified object cannot be converted to string.', 1700819706);
        }
        if ($hrefsInBrackets === true) {
            $value = preg_replace_callback(
                '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1[^>]*>(.*?)<\/a>/i',
                static function($matches) use ($allowedTags) {
                    if (count($matches) >= 4) {
                        [$url, $text] = array_map('trim', [$matches[2], $matches[3]]);
                        if ($text !== '' && $url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                            return '"' . strip_tags((string)$text, $allowedTags) . ' [' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . ']"';
                        } else if ($text !== '') {
                            return $text;
                        }
                    }
                    return '';
                }, $value
            );
        }
        return strip_tags((string)$value, $allowedTags);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
