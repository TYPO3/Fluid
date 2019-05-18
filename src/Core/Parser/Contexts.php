<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

/**
 * Fluid Contexts
 *
 * Container (alternative to associative array) which carries individual
 * Context objects which provide bit masks used for matching characters,
 * and get stacked when the sequencer enters a new context.
 */
class Contexts
{
    /** @var Context */
    public $root;

    /** @var Context */
    public $inline;

    /** @var Context */
    public $tag;

    /** @var Context */
    public $array;

    /** @var Context */
    public $quoted;

    /** @var Context */
    public $attributes;

    /** @var Context */
    public $dead;

    public function __construct()
    {
        // Root context: aware of tag start or inline start only.
        $this->root = new Context(
            Context::CONTEXT_ROOT,
            Splitter::MASK_TAG_OPEN,
            Splitter::MASK_INLINE_OPEN
        );

        // Inline context: aware of array syntax, sub-inline syntax, inline VH syntax, and arguments enclosed by parenthesis by looking for parenthesis start
        $this->inline = new Context(
            Context::CONTEXT_INLINE,
            Splitter::MASK_PARENTHESIS_START | Splitter::MASK_INLINE_LEGACY_PASS | Splitter::MASK_WHITESPACE | Splitter::MASK_COLON | Splitter::MASK_QUOTES | Splitter::MASK_SEPARATORS,
            Splitter::MASK_INLINE_OPEN | Splitter::MASK_INLINE_END | Splitter::MASK_INLINE_PASS
        );

        // Tag: entered into when a detected tag has a namespace operator in tag name
        $this->tag = new Context(
            Context::CONTEXT_TAG,
            Splitter::MASK_TAG_END | Splitter::MASK_COLON | Splitter::MASK_TAG_CLOSE | Splitter::MASK_WHITESPACE,
            Splitter::MASK_INLINE_OPEN
        );

        // Parenthesis context: aware of separators, key/value assignments, the end of a parenthesis and quotation marks. Is used for both
        // parenthesis arguments for inline syntax and tag attribute arguments for tag syntax.
        $this->array = new Context(
            Context::CONTEXT_ARRAY,
            Splitter::MASK_PARENTHESIS_END | Splitter::MASK_SEPARATORS | Splitter::MASK_QUOTES,
            Splitter::MASK_INLINE_OPEN | Splitter::MASK_INLINE_END | Splitter::MASK_ARRAY
        );

        // Quoted: entered into when a quote mark (single or double) is encountered (in an array which includes in tag arguments)
        $this->quoted = new Context(
            Context::CONTEXT_QUOTED,
            Splitter::MASK_QUOTES,
            Splitter::MASK_INLINE_OPEN
        );

        // Attributes: identical to array, except does not ignore whitespace and does not split on array [] characters
        $this->attributes = new Context(
            Context::CONTEXT_ATTRIBUTES,
            Splitter::MASK_QUOTES | Splitter::MASK_TAG_CLOSE | Splitter::MASK_TAG_END | Splitter::MASK_WHITESPACE | Splitter::MASK_SEPARATORS,
            Splitter::MASK_INLINE_OPEN | Splitter::MASK_INLINE_END
        );
        // Attributes: identical to array, except does not ignore whitespace and does not split on array [] characters
        $this->dead = new Context(
            Context::CONTEXT_DEAD,
            Splitter::MASK_TAG_END,
            Splitter::MASK_INLINE_OPEN
        );
    }
}
