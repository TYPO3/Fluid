<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

/**
 * Splitter
 *
 * Byte-based calculations to perform splitting on Fluid template sources.
 * Uses (64bit) bit masking to detect characters that may split a template,
 * by grouping "interesting" bytes which have ordinal values within a value
 * range of maximum 64 and comparing the bit mask of this and the byte being
 * analysed.
 *
 * Contains the methods needed to iterate and match bytes based on (mutating)
 * bit-masks, and a couple of shorthand "peek" type methods to determine if
 * the current yield should be a certain type or another.
 *
 * The logic is essentially the equivalent of:
 *
 * - Using arrays of possible byte values
 * - Iterating characters and checking against the must-match bytes
 * - Using "substr" to extract relevant bits of template code
 *
 * The difference is that the method in this class is excessively faster than
 * any array-based counterpart and consumes orders of magnitude less memory.
 * It also means the opcode optimised version of the loop and comparisons use
 * ideal CPU instructions at the bit-level instead, making them both smaller
 * and even more efficient when compiled.
 *
 * Works by:
 *
 * - Iterating a byte value array while maintaining an internal pointer
 * - Whenever encountering split characters, produce output that includes
 *   the split character for example a Part might contain {foo}
 * - On-the-fly exchanging the bit masks used for calculation to perform
 *   all splitting in a single loop without function calls.
 */
class Splitter
{
    /** @var Debugger|null */
    public $debugger;

    public const MAX_NAMESPACE_LENGTH = 10;

    public const BYTE_NULL = 0; // Zero-byte for terminating documents
    public const BYTE_INLINE = 123; // The "{" character indicating an inline expression started
    public const BYTE_INLINE_END = 125; // The "}" character indicating an inline expression ended
    public const BYTE_PIPE = 124; // The "|" character indicating an inline expression pass operation
    public const BYTE_MINUS = 45; // The "-" character (for legacy pass operations)
    public const BYTE_TAG = 60; // The "<" character indicating a tag has started
    public const BYTE_TAG_END = 62; // The ">" character indicating a tag has ended
    public const BYTE_TAG_CLOSE = 47; // The "/" character indicating a tag is a closing tag
    public const BYTE_QUOTE_DOUBLE = 34; // The " (standard double-quote) character
    public const BYTE_QUOTE_SINGLE = 39; // The ' (standard single-quote) character
    public const BYTE_WHITESPACE_SPACE = 32; // A standard space character
    public const BYTE_WHITESPACE_TAB = 9; // A standard carriage-return character
    public const BYTE_WHITESPACE_RETURN = 13; // A standard tab character
    public const BYTE_WHITESPACE_EOL = 10; // A standard (UNIX) line-break character
    public const BYTE_SEPARATOR_EQUALS = 61; // The "=" character
    public const BYTE_SEPARATOR_COLON = 58; // The ":" character
    public const BYTE_SEPARATOR_COMMA = 44; // The "," character
    public const BYTE_SEPARATOR_PIPE = 124; // The "|" character
    public const BYTE_PARENTHESIS_START = 40; // The "(" character
    public const BYTE_PARENTHESIS_END = 41; // The ")" character
    public const BYTE_ARRAY_START = 91; // The "[" character
    public const BYTE_ARRAY_END = 93; // The "]" character
    public const BYTE_SLASH = 47; // The "/" character
    public const BYTE_BACKSLASH = 92; // The "\" character

    // Amount to shift mid-bytes to low-bytes (123 - 64 = 59 as mapped byte value)
    public const MAP_SHIFT = 64;

    // A bit mask consisting of "<" and ">"
    public const MASK_TAG_OPEN = 0 | (1 << self::BYTE_TAG);
    public const MASK_TAG_CLOSE = 0 | (1 << self::BYTE_TAG_CLOSE);
    public const MASK_TAG_END = 0 | (1 << self::BYTE_TAG_END);
    #public const MASK_TAG = 0 | (1 << self::BYTE_TAG) | (1 << self::BYTE_TAG_END);
    // And one consisting of { and }. This set of bits are subtracted by 64 and will only be used in a special comparison
    // which also subtracts 64 after confirming the original byte value was > 64.
    public const MASK_INLINE_OPEN = 0 | (1 << (self::BYTE_INLINE - self::MAP_SHIFT));
    public const MASK_INLINE_END = 0 | (1 << (self::BYTE_INLINE_END - self::MAP_SHIFT));
    public const MASK_INLINE_PASS = 0 | (1 << (self::BYTE_PIPE - self::MAP_SHIFT));
    public const MASK_INLINE_LEGACY_PASS = 0 | (1 << self::BYTE_MINUS) | (1 << self::BYTE_TAG_END);
    #public const MASK_INLINE = 0 | self::MASK_INLINE_OPEN | self::MASK_INLINE_END;

    // A bit mask consisting of " and ' to match quotes
    public const MASK_QUOTES = 0 | (1 << self::BYTE_QUOTE_DOUBLE) | (1 << self::BYTE_QUOTE_SINGLE);

    // A bit mask consisting of: (space) (tab) (carriage return)
    public const MASK_WHITESPACE = 0 | (1 << self::BYTE_WHITESPACE_TAB) | (1 << self::BYTE_WHITESPACE_EOL) | (1 << self::BYTE_WHITESPACE_RETURN) | (1 << self::BYTE_WHITESPACE_SPACE);
    public const MASK_LINEBREAKS = 0 | (1 << self::BYTE_WHITESPACE_EOL) | (1 << self::BYTE_WHITESPACE_RETURN);

    // A bit mask consisting of "=" and ":" and ","
    public const MASK_SEPARATORS = 0 | (1 << self::BYTE_SEPARATOR_EQUALS) | (1 << self::BYTE_SEPARATOR_COLON) | (1 << self::BYTE_SEPARATOR_COMMA);
    public const MASK_COMMA = 0 | (1 << self::BYTE_SEPARATOR_COMMA);
    public const MASK_EQUALS = 0 | (1 << self::BYTE_SEPARATOR_EQUALS);

    // A bit mask consisting of ( and )
    #public const MASK_PARENTHESIS = 0 | (1 << self::BYTE_PARENTHESIS_START) | (1 << self::BYTE_PARENTHESIS_END);
    public const MASK_PARENTHESIS_START = 0 | (1 << self::BYTE_PARENTHESIS_START);
    public const MASK_PARENTHESIS_END = 0 | (1 << self::BYTE_PARENTHESIS_END);

    public const MASK_COLON = 0 | (1 << self::BYTE_SEPARATOR_COLON);
    public const MASK_MINUS = 0 | (1 << self::BYTE_MINUS);
    public const MASK_BACKSLASH = 0 | (1 << (self::BYTE_BACKSLASH - self::MAP_SHIFT));

    // A bit mask consisting of [ and ]
    public const MASK_ARRAY = 0 | (1 << (self::BYTE_ARRAY_START - self::MAP_SHIFT)) | (1 << (self::BYTE_ARRAY_END - self::MAP_SHIFT));

    /** @var Position */
    public $position;

    /** @var Source */
    public $source;

    public function __construct(Position $position, Source $source)
    {
        $this->position = $position;
        $this->source = $source;
    }

    /**
     * Split a string by searching for recognized characters using at least one,
     * optionally two bit masks consisting of OR'ed bit values of each detectable
     * character (byte). The secondary bit mask is costless as it is OR'ed into
     * the primary bit mask.
     *
     * @return \Generator
     */
    public function parse(): \Generator
    {
        $bytes = &$this->source->bytes;

        if (empty($bytes)) {
            yield Splitter::BYTE_NULL => $this->position;
            return;
        }

        $index = &$this->position->index;
        $length = $this->source->length;
        $primaryMask = $this->position->context->primaryMask;
        $secondaryMask = $this->position->context->secondaryMask;
        $mask = $primaryMask | $secondaryMask;

        for (; $index <= $length; ++$index) {
            // Strip the highest byte, mapping >64 byte values to <64 ones which will be recognized by the bit mask.
            // A match only means that we have encountered a potentially interesting character.
            // alternative method: if (($mask >> ($byte & 63) & 1)
            if ($mask & (1 << ($bytes[$index] & 63))) {

                $byte = $bytes[$index];

                // Decide which byte we encountered by explicitly checking if the encountered byte was in the minimum
                // range (not-mapped match). Next check is if the matched byte is within 64-128 range in which case
                // it is a mapped match. Anything else (>128) will be non-ASCII that is always captured.
                if (($primaryMask & (1 << $byte)) && $byte < 64) {
                    yield $byte => $this->position->copy();
                    $this->position->lastYield = $index;
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(str_repeat(' ', count($this->position->stack)) . chr($byte) . ' ' . $this->position->getContextName(), 31);
                    }
                    $primaryMask = $this->position->context->primaryMask;
                    $secondaryMask = $this->position->context->secondaryMask;
                    $mask = $primaryMask | $secondaryMask;
                } elseif (($secondaryMask & (1 << ($byte - static::MAP_SHIFT))) && $byte < 128) {
                    yield $byte => $this->position->copy();
                    $this->position->lastYield = $index;
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(str_repeat(' ', count($this->position->stack)) . chr($byte) . ' ' . $this->position->getContextName(), 35);
                    }
                    $primaryMask = $this->position->context->primaryMask;
                    $secondaryMask = $this->position->context->secondaryMask;
                    $mask = $primaryMask | $secondaryMask;
                } else {
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(str_repeat(' ', count($this->position->stack)) . chr($byte) . ' ' . $this->position->getContextName(), 36);
                    }
                }

            } else {
                if ($this->debugger) {
                    $this->debugger->writeLogLine(str_repeat(' ', count($this->position->stack)) . chr($bytes[$index]) . ' ' . $this->position->getContextName(), 37);
                }
            }
        }

        if (count($this->position->stack) > 1) {
            throw new \RuntimeException(
                sprintf(
                    'Unterminated expression started at index %d - context stack still contains %d elements: %s',
                    $this->position->lastYield,
                    count($this->position->stack),
                    var_export($this->position->stack, true)
                )
            );
        }

        if ($this->position->lastYield < $this->position->index) {
            ++$this->position->index;
            yield Splitter::BYTE_NULL => $this->position;
        }
    }

    public function countCharactersMatchingMask(int $primaryMask, int $offset, int $length): int
    {
        $bytes = &$this->source->bytes;
        $counted = 0;
        for ($index = $offset; $index < $this->source->length; $index++) {
            if (($primaryMask & (1 << $bytes[$index])) && $bytes[$index] < 64) {
                $counted++;
            }
        }
        return $counted;
    }

    public function findBytePositionBeforeOffset(int $primaryMask, int $offset): int
    {
        $bytes = &$this->source->bytes;
        for ($index = min($offset, $this->source->length); $index > 0; $index--) {
            if (($primaryMask & (1 << $bytes[$index])) && $bytes[$index] < 64) {
                return $index;
            }
        }
        return 0;
    }

    public function findBytePositionAfterOffset(int $primaryMask, int $offset): int
    {
        $bytes = &$this->source->bytes;
        for ($index = $offset; $index < $this->source->length; $index++) {
            if (($primaryMask & (1 << $bytes[$index])) && $bytes[$index] < 64) {
                return $index;
            }
        }
        return max($this->source->length, $offset);
    }

    /**
     * Shorthand alias for seekBeforeMatch which prepares the bit masks
     * required to match a colon coming before any of the following:
     *
     * - A tag end ">" character
     * - A tag close "/" character
     * - An inline arguments start "(" character
     * - An inline pass operator (or legacy pass operator) - "|" or ">" or "-" character
     *   to catch cases like "{var->v:h()}"
     * - Any whitespace which would not match a tag with attributes like "<tag attr='foo:x'>"
     *
     * A return value that is *DIFFERENT FROM OFFSET* means a colon was
     * found before any of the above bytes.
     *
     * @param int $searchLength The maximum number of bytes to scan before returning $offset (meaning "not found")
     * @return int
     */
    public function seekColon(int $searchLength): int
    {
        $offset = $this->seekMatchBeforeMatch(
            static::MASK_COLON,
            0,
            static::MASK_WHITESPACE | static::MASK_INLINE_LEGACY_PASS,
            static::MASK_INLINE_END | static::MASK_INLINE_PASS,
            $this->position->index,
            $searchLength
        );
        return $offset;
    }

    /**
     * Shorthand alias for seekBeforeMatch which prepares the bit masks
     * required to determine if the expression starting at current
     * position is an inline syntax (vs. an array).
     *
     * Returns TRUE if the expression is an inline expression (which it
     * is after scan, $offset remains the same as current index).
     *
     * @param int $searchLength
     * @return bool
     */
    public function seekInlineQualifier(int $searchLength): bool
    {
        // An inline expression is possible if we find:
        // - an inline pass, colon, parenthesis start, ending or opening curly brace coming before a separator (comma, equals but not colon) or quotes
        // Except if we find a colon - then we need to check:
        // - a parenthesis open before whitespace, ending or opening curly brace
        $offset = $this->seekMatchBeforeMatch(
            static::MASK_PARENTHESIS_START | static::MASK_COLON,
            static::MASK_INLINE_PASS | static::MASK_INLINE_OPEN | static::MASK_INLINE_END,
            static::MASK_COMMA | static::MASK_EQUALS,
            0,
            $this->position->index + 1,
            $searchLength
        );

        if ($this->source->bytes[$offset] === Splitter::BYTE_SEPARATOR_COLON) {
            $colonPosition = $offset;
            $colonScanStart = $colonPosition + 1;
            // If we detected a colon we need to determine one thing: will a parenthesis open come before whitespace or curly braces
            // after the colon.
            $offset = $this->seekMatchBeforeMatch(
                static::MASK_PARENTHESIS_START,
                0,
                static::MASK_WHITESPACE,
                static::MASK_INLINE_OPEN | static::MASK_INLINE_END,
                $colonScanStart,
                $searchLength
            );
            if ($offset === $colonScanStart) {
                // Parenthesis open NOT found - the expression is not inline.
                return false;
            }
        }
        $isInline = $offset !== $this->position->index;
        return $isInline;
    }

    /**
     * Searches out an identified byte, by bit mask, in bytes of source
     * starting from $offset and scanning a maximum of $searchLength
     * bytes ahead from $offset. Takes a secondary pair of masks which
     * if found before the main mask, causes $offset to be returned.
     *
     * Returns the index at which a matching byte was found, or returns
     * $offset if the mask was not matched (before the negative mask).
     *
     * @param int $primaryMask Bit mask of byte values < 64 to scan for
     * @param int $secondaryMask Bit mask of byte values > 64 to scan for
     * @param int $primaryNegativeMask Bit mask of byte values < 64 to scan for which if found before main masks, causes return of $offset
     * @param int $secondaryNegativeMask Bit mask of byte values > 64 to scan for which if found before main masks, causes return of $offset
     * @param int $offset Index from which to start scanning
     * @param int $searchLength Maximum number of bytes to scan
     * @return int
     */
    public function seekMatchBeforeMatch(int $primaryMask, int $secondaryMask, int $primaryNegativeMask, int $secondaryNegativeMask, int $offset, int $searchLength): int
    {
        $bytes = &$this->source->bytes;
        $max = $offset + $searchLength;

        $mask = $primaryNegativeMask | $secondaryNegativeMask | $primaryMask | $secondaryMask;

        for ($index = $offset; $index < $max; $index++) {

            // Strip the highest byte, mapping >64 byte values to <64 ones which will be recognized by the bit mask.
            // A match only means that we have encountered a potentially interesting character.
            // alternative method: if (($mask >> ($byte & 63) & 1)
            if ($mask & (1 << ($bytes[$index] & 63))) {

                $byte = $bytes[$index];

                // Decide which byte we encountered by explicitly checking if the encountered byte was in the minimum
                // range (not-mapped match). Next check is if the matched byte is within 64-128 range in which case
                // it is a mapped match. Anything else (>128) will be non-ASCII that is always captured.
                if (($primaryNegativeMask & (1 << $byte)) && $byte < 64) {
                    return $offset;
                } elseif ($byte < 128 && $byte > 64 && ($secondaryNegativeMask & (1 << ($byte - static::MAP_SHIFT)))) {
                    return $offset;
                } elseif (($primaryMask & (1 << $byte)) && $byte < 64) {
                    return $index;
                } elseif ($byte < 128 && $byte > 64 && ($secondaryMask & (1 << ($byte - static::MAP_SHIFT)))) {
                    return $index;
                }
            }
        }

        return $offset;
    }
}
