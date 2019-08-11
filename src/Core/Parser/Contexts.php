<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
    public $data;

    /** @var Context */
    public $toggle;

    /** @var Context */
    public $array;

    /** @var Context */
    public $quoted;

    /** @var Context */
    public $attributes;

    /** @var Context */
    public $dead;

    /** @var Context */
    public $protected;

    /** @var Context */
    public $accessor;

    /** @var Context */
    public $empty;

    /** @var Context */
    public $boolean;

    /** @var Context */
    public $inactive;

    public function __construct()
    {
        // Root context: aware of tag start or inline start only, and escapes (to ignore next symbol).
        $this->root = new Context(Context::CONTEXT_ROOT, '{<\\');

        // Inline context: aware of array syntax, sub-inline syntax, inline VH syntax, and arguments enclosed by parenthesis by looking for parenthesis start
        $this->inline = new Context(Context::CONTEXT_INLINE, "@(->[]|{}:,=|\\\t\n\r\0'\"` ");

        // Tag: entered into when a detected tag has a namespace operator in tag name
        $this->tag = new Context(Context::CONTEXT_TAG, "[>:{ /\t\n\r\0");

        // P/CDATA: entered into when a detected tag starts with ![CDATA[ or ![PCDATA[ - exclusively matches end of tag.
        $this->data = new Context(Context::CONTEXT_DATA, '>');

        // Fluid feature toggles: an inline prefixed with at sign, e.g. {@escaping off}, which supports only whitespace and ending curly brace symbols.
        $this->toggle = new Context(Context::CONTEXT_TOGGLE, ' }');

        // Parenthesis context: aware of separators, key/value assignments, the end of a parenthesis and quotation marks. Is used for both
        // parenthesis arguments for inline syntax and tag attribute arguments for tag syntax.
        $this->array = new Context(Context::CONTEXT_ARRAY, ":,]}=\"'`[{)\\ \t\r\n");

        // Quoted: entered into when a quote mark (single or double) is encountered (in an array which includes in tag arguments)
        $this->quoted = new Context(Context::CONTEXT_QUOTED, '\\"\'{[`');

        // Attributes: identical to array, except does not ignore whitespace and does not split on array [] characters
        $this->attributes = new Context(Context::CONTEXT_ATTRIBUTES, " >:{\"'/,=\t\r\n}");

        // Dead tag: continues indexing until the tag ends or an inline expression is encountered
        $this->dead = new Context(Context::CONTEXT_DEAD, '>{');

        // Inline protection mode: reacts to backtick and curly brace start and end, but ignores nested curly braces. Backtick enters quoted context. Still allows expressions to match.
        $this->protected = new Context(Context::CONTEXT_PROTECTED, ' {}`');

        // Inline accessor mode: identical to protected context but identifiable under its own name to distinguish it from protected
        $this->accessor = new Context(Context::CONTEXT_ACCESSOR, '{}`');

        // Empty: matches no characters, only possible yield is BYTE_NULL.
        $this->empty = new Context(Context::CONTEXT_EMPTY, '');

        // Boolean: matches parenthesis groups, backslash, inline expressions, quotes and whitespace (which separates expression parts but is not quoted).
        $this->boolean = new Context(Context::CONTEXT_BOOLEAN, "{()'\"\\ \t\r\n");

        // Inactive: a context that will only match tag open and close, ignoring everything else
        $this->inactive = new Context(Context::CONTEXT_INACTIVE, '<>');
    }
}
