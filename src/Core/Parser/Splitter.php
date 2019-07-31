<?php
declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

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
    public const BYTE_NULL = 0; // Zero-byte for terminating documents
    public const MAP_SHIFT = 64;

    /** @var Source */
    public $source;

    /** @var Context */
    public $context;

    /** @var Contexts */
    public $contexts;

    public $index = 0;
    private $primaryMask = 0;
    private $secondaryMask = 0;

    public function __construct(Source $source, Contexts $contexts)
    {
        $this->source = $source;
        $this->contexts = $contexts;
        $this->switch($contexts->root);
    }

    /**
     * Split a string by searching for recognized characters using at least one,
     * optionally two bit masks consisting of OR'ed bit values of each detectable
     * character (byte). The secondary bit mask is costless as it is OR'ed into
     * the primary bit mask.
     *
     * @return \NoRewindIterator|string[]|null[]
     */
    public function parse(): \NoRewindIterator
    {
        return new \NoRewindIterator($this->createGenerator());
    }

    /**
     * Split a string by searching for recognized characters using at least one,
     * optionally two bit masks consisting of OR'ed bit values of each detectable
     * character (byte). The secondary bit mask is costless as it is OR'ed into
     * the primary bit mask.
     *
     * @return \NoRewindIterator|string[]|null[]
     */
    protected function createGenerator(): \Generator
    {
        $bytes = &$this->source->bytes;
        $source = &$this->source->source;

        if (empty($bytes)) {
            yield Splitter::BYTE_NULL => null;
            return;
        }

        $captured = null;

        foreach ($bytes as $this->index => $byte) {
            // Decide which byte we encountered by explicitly checking if the encountered byte was in the minimum
            // range (not-mapped match). Next check is if the matched byte is within 64-128 range in which case
            // it is a mapped match. Anything else (>128) will be non-ASCII that is always captured.
            if ($byte < 64 && ($this->primaryMask & (1 << $byte))) {
                yield $byte => $captured;
                $captured = null;
            } elseif ($byte >= 64 && $byte < 128 && ($this->secondaryMask & (1 << ($byte - static::MAP_SHIFT)))) {
                yield $byte => $captured;
                $captured = null;
            } else {
                // Append captured bytes from source, must happen after the conditions above so we avoid appending tokens.
                $captured .= $source[$this->index - 1];
            }
        }
        if ($captured !== null) {
            yield Splitter::BYTE_NULL => $captured;
        }
    }

    public function switch(Context $context): Context
    {
        $previous = $this->context;
        $this->context = $context;
        $this->primaryMask = $context->primaryMask;
        $this->secondaryMask = $context->secondaryMask;
        return $previous ?? $context;
    }
}
