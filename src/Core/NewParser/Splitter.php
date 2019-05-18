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
 * - Yielding byte and position (which contains captured text since last yield)
 * - When yielding, reload the bit masks used in the next iteration
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
     * @return \Generator|Position[]
     */
    public function parse(): \Generator
    {
        $bytes = &$this->source->bytes;

        if (empty($bytes)) {
            yield Splitter::BYTE_NULL => $this->position;
            return;
        }

        $source = &$this->source->source;
        $index = &$this->position->index;
        $length = $this->source->length + 1;
        $primaryMask = $this->position->context->primaryMask;
        $secondaryMask = $this->position->context->secondaryMask;
        $mask = $primaryMask | $secondaryMask;
        $captured = null;

        for (; $index < $length; ++$index) {
            // Strip the highest byte, mapping >64 byte values to <64 ones which will be recognized by the bit mask.
            // A match only means that we have encountered a potentially interesting character.
            // alternative method: if (($mask >> ($byte & 63) & 1)
            if ($mask & (1 << ($bytes[$index] & 63))) {

                $byte = $bytes[$index];

                // Decide which byte we encountered by explicitly checking if the encountered byte was in the minimum
                // range (not-mapped match). Next check is if the matched byte is within 64-128 range in which case
                // it is a mapped match. Anything else (>128) will be non-ASCII that is always captured.
                if ($byte < 64 && ($primaryMask & (1 << $byte))) {
                    yield $byte => $this->position->copy($captured);
                    $this->position->lastYield = $index;
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(chr($byte) . ' ' . $this->position->getContextName(), 31);
                    }
                    $primaryMask = $this->position->context->primaryMask;
                    $secondaryMask = $this->position->context->secondaryMask;
                    $mask = $primaryMask | $secondaryMask;
                    continue;
                } elseif ($byte > 64 && $byte < 128 && ($secondaryMask & (1 << ($byte - static::MAP_SHIFT)))) {
                    yield $byte => $this->position->copy($captured);
                    $this->position->lastYield = $index;
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(chr($byte) . ' ' . $this->position->getContextName(), 35);
                    }
                    $primaryMask = $this->position->context->primaryMask;
                    $secondaryMask = $this->position->context->secondaryMask;
                    $mask = $primaryMask | $secondaryMask;
                    continue;
                } else {
                    if ($this->debugger) {
                        $this->debugger->writeLogLine(chr($byte) . ' ' . $this->position->getContextName(), 36);
                    }
                }

            } else {
                if ($this->debugger) {
                    $this->debugger->writeLogLine(chr($bytes[$index]) . ' ' . $this->position->getContextName(), 37);
                }
            }

            // Append captured bytes from source, must happen after the conditions above so we avoid appending tokens.
            $captured .= $source{$index - 1};
        }

        if ($this->position->lastYield < $this->source->length) {
            yield Splitter::BYTE_NULL => $this->position->copy($captured);
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
}
