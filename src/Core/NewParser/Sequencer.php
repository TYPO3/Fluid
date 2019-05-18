<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

/**
 * Fluid Byte Sequencer
 *
 * Class responsible for managing the bit masks used when the
 * Splitter class iterates over each byte of the template source
 * code. Works by analysing each "yield" from the inner generator
 * and determining whether or not to enter, leave or switch the
 * bit mask (context-dependent) the Splitter uses.
 *
 * Maintains an array stack of "Context" classes which is pushed
 * to when entering a new context, popped when leaving a context,
 * and not touched when switching a context.
 *
 * The Sequencer's most important responsibilities are:
 *
 * 1. Determine whether or not to yield an active symbol with an
 *    associated position pointing to the template source code.
 * 2. Updating the "when did I last yield?" information on the
 *    position so that when the position is cloned, the result
 *    is a separate instance of a light-weight position that has
 *    two correct offsets: the byte index where a yield last
 *    happened, and the index where the new yield happened.
 *
 * The cloned position, having now permanent index offset values,
 * can then be used at any time later on to extract the exact
 * required byte sequence (as ASCII string) by using the
 * ByteSequence object.
 */
class Sequencer
{
    public $source;
    public $position;

    private $splitter;

    /** @var Contexts */
    private $contexts;

    public function __construct(string $source, ?Debugger $debugger = null)
    {
        $this->contexts = new Contexts();
        $this->position = new Position($this->contexts->root);
        $this->source = new Source($source);
        $this->splitter = new Splitter($this->position, $this->source);
        $this->splitter->debugger = $debugger;
    }

    /**
     * Creates a dump, starting from the first line break before $position,
     * to the next line break from $position, counting the lines and characters
     * and inserting a marker pointing to the exact offending character.
     *
     * Is not very efficient - but adds bug tracing information. Should only
     * be called when exceptions are raised during sequencing.
     * 
     * @param Position $position
     * @return string
     */
    public function extractSourceDumpOfLineAtPosition(Position $position): string
    {
        $lines = $this->splitter->countCharactersMatchingMask(Splitter::MASK_LINEBREAKS, 1, $position->index) - 1 ?: 1;
        $crop = new Position(
            $position->context,
            $this->splitter->findBytePositionBeforeOffset(Splitter::MASK_LINEBREAKS, $position->index),
            $this->splitter->findBytePositionAfterOffset(Splitter::MASK_LINEBREAKS, $position->index)
        );
        $line = $this->pack($crop);
        $character = $position->index - $crop->lastYield - 1;
        $string = 'Line ' . $lines . ' character ' . $character . PHP_EOL;
        $string .= PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . 'v' . PHP_EOL;
        $string .= trim($line) . PHP_EOL;
        $string .= str_repeat(' ', max($character, 0)) . '^' . PHP_EOL;
        return $string;
    }

    /**
     * Pack byte sequence defined by Position
     *
     * Small utility that can "pack" a Position object pointing to
     * offsets in the Source object's byte array, creating an ASCII
     * string to be returned.
     *
     * Accepts bit masks like the Splitter, which can be used to
     * pass undesired byte values that will be ignored.
     *
     * Essentially a highly optimised version of doing:
     *
     *     trim(pack('C*', ...array_slice($bytes, $offset, $length)), implode('', $undesiredChars))
     *
     * Or:
     *
     *     trim(substr($source, $offset, $length), implode('', $undesiredChars));
     *
     * But many, many times faster than either of those methods.
     *
     * Caveat: contrary to what "trim" would do, this method will
     * ignore the unwanted bytes regardless of where in the string
     * they exist - start, end or anywhere inside other characters.
     * This behavior is fine for sequencing Fluid where symbols are
     * yielded in a way that unwanted characters would already have
     * caused a split, but probably not good for other use cases.
     *
     * Note: the longer the extraction sequence is, the better this
     * class performs compared to either of the methods above. The
     * method of slicing and packing the byte array is by far the
     * least optimal. But only this method has the benefit of not
     * creating any memory copies except for the exact bytes that
     * must be returned - and uses a bit based replacement for the
     * trim() method which avoids adding, instead of removing after,
     * any unwanted bytes.
     */
    public function pack(Position $position, int $ignorePrimaryMask = 0, int $ignoreSecondaryMask = 0): ?string
    {
        $offset = $position->lastYield + 1;
        $end = $position->index;
        $source = &$this->source->source;
        $captured = null;
        for ($index = $offset; $index < $end; $index++) {
            $byte = $this->source->bytes[$index];
            if ($ignorePrimaryMask > 0 && $byte < 64 && ($ignorePrimaryMask & (1 << $byte))) {
                continue;
            } elseif ($ignoreSecondaryMask > 0 && $byte > 64 && $byte < 128 && ($ignoreSecondaryMask & (1 << ($byte - Splitter::MAP_SHIFT)))) {
                continue;
            }
            $captured .= $source{$index - 1};
        }
        return $captured;
    }

    /**
     * Main and only sequencing loop. Iterates the Splitter's yielded
     * symbols and positions and switches/stacks contexts accordingly.
     *
     * @return \Generator|Position[]
     */
    public function sequence(): \Generator
    {
        if (empty($this->source->bytes)) {
            yield Splitter::BYTE_NULL => $this->position;
            return;
        }

        $spool = [];

        try {
            foreach ($this->splitter->parse() as $token => $captured) {
                switch ($token) {

                    // Cases for "/"
                    case Splitter::BYTE_TAG_CLOSE:
                    case Splitter::BYTE_NULL:
                        yield $token => $captured;
                        break;

                    // Case: "{" token. Start spooling until we know enough tokens to decide if we are in inline or
                    // array context.
                    case Splitter::BYTE_INLINE:
                        if ($this->position->context->context === Context::CONTEXT_ARRAY) {
                            // This sub-case means we have encountered inline syntax inside array context (a sub-array)
                        #    yield from $this->spool($spool, [Context::CONTEXT_INLINE => Context::CONTEXT_ARRAY], [Splitter::BYTE_INLINE => Splitter::BYTE_ARRAY_START]);
                        }
                        $spool[] = [$token, $captured];
                        $this->position->enter($this->contexts->inline);
                        break;

                    // Case: "}" token - always means exit whatever current context is and go up one stack level.
                    case Splitter::BYTE_INLINE_END:
                        if ($this->position->context->context === Context::CONTEXT_ARRAY) {
                            yield from $this->spool($spool, [Context::CONTEXT_INLINE => Context::CONTEXT_ARRAY], [Splitter::BYTE_INLINE => Splitter::BYTE_ARRAY_START]);
                            yield Splitter::BYTE_ARRAY_END => $captured;
                        } else {
                            yield from $this->spool($spool, [], [Splitter::BYTE_ARRAY_START => Splitter::BYTE_INLINE]);
                            yield $token => $captured;
                        }
                        $this->position->leave();
                        break;

                    // Case: "<" token. This case has a check for context since tags are only supported in root-level
                    // contexts. After sanity check the case seeks for a colon as part of the tag name which indicates
                    // it should use a more detailed bit mask causing arguments to be found.
                    case Splitter::BYTE_TAG:
                        yield $token => $captured;
                        $this->position->enter($this->contexts->tag);
                        break;

                    // This byte may occur in two different contexts: tag context, or inline. If it occurs in inline
                    // context this means we have a "legacy pass" operator "->" which *should* be migrated to "|"
                    // but still is supported. If the context is inline the byte will NOT cause a new context to
                    // be entered. If it is anywhere else, it means the current context is exited.
                    case Splitter::BYTE_TAG_END:

                        if ($captured->context->context === Context::CONTEXT_INLINE) {
                            #$spool[] = [Splitter::BYTE_PIPE, $captured];
                            yield from $this->spool($spool, [Context::CONTEXT_ARRAY => Context::CONTEXT_INLINE], [Splitter::BYTE_ARRAY_START => Splitter::BYTE_INLINE]);
                            yield Splitter::BYTE_PIPE => $captured;
                        } elseif ($captured->context->context === Context::CONTEXT_ARRAY) {
                            #$spool[] = [Splitter::BYTE_PIPE, $captured];
                            $this->position->switch($this->contexts->inline);
                            yield from $this->spool($spool, [Context::CONTEXT_ARRAY => Context::CONTEXT_INLINE], [Splitter::BYTE_ARRAY_START => Splitter::BYTE_INLINE]);
                            yield Splitter::BYTE_PIPE => $captured;
                        } else {
                            yield $token => $captured;
                            $this->position->leave();
                            if ($this->position->context->context === Context::CONTEXT_TAG) {
                                // We remain inside ViewHelper arguments - leave one additional level of the stack.
                                $this->position->leave();
                            }
                        }
                        break;

                    # QUOTE CASES - handling of open and start quotes.

                    // Case for quotes (single and double). If outside quoted context we enter a new context.
                    // If inside quoted context we check if the quote matched the opening quote, then return it.
                    // If it does not match the opening quote we continue scanning as if the quote a normal character.
                    case Splitter::BYTE_QUOTE_DOUBLE:
                    case Splitter::BYTE_QUOTE_SINGLE:
                        /*
                        $last = end($spool);
                        if ($last !== false) {
                            $lastByte = ($last[0] ?? 0);
                            if ($lastByte === Splitter::BYTE_SEPARATOR_COLON || $lastByte === Splitter::BYTE_INLINE || $lastByte === Splitter::BYTE_SEPARATOR_COMMA) {
                                #$spool[] = [$token, $captured];
                                yield from $this->spool($spool, [Context::CONTEXT_INLINE => Context::CONTEXT_ARRAY], [Splitter::BYTE_INLINE => Splitter::BYTE_ARRAY_START]);
                                #$this->position->switch($this->contexts->array);
                            } else {
                                $spool[] = [$token, $captured];
                                yield from $this->spool($spool);
                                #$this->position->switch($this->contexts->array);
                            }
                        }
                        */

                        yield from $this->spool($spool, [Context::CONTEXT_INLINE => Context::CONTEXT_ARRAY], [Splitter::BYTE_INLINE => Splitter::BYTE_ARRAY_START]);
                        if ($this->position->context->context !== Context::CONTEXT_QUOTED) {
                            yield $token => $captured;
                            $this->position->enter($this->contexts->quoted, $token);
                        } elseif ($this->position->byteMatchesStartingByteOfTopmostStackElement($token)) {
                            yield $token => $captured;
                            $this->position->leave();
                        }
                        break;

                    # BACKSLASH AND LEGACY PASS MINUS PREFIX CASES

                    // Case for handling a backslash character, which only has any meaning when inside quoted context.
                    // If the context is right and the next character is not also a backslash, the function is to skip
                    // the next byte (which would be a quote because we already filtered out sub-inline syntax above)
                    // and continue with the same bit mask and context.
                    case Splitter::BYTE_BACKSLASH:
                    case Splitter::BYTE_MINUS:
                        break;

                    # TAG CASE - handling of tags. BYTE_MINUS is added here since it only has relevance for the legacy
                    # inline passing operator that is handled by BYTE_TAG_END. When legacy pass support is dropped this
                    # case and the check inside BYTE_TAG_END may be removed.

                    case Splitter::BYTE_ARRAY_END:
                    case Splitter::BYTE_PARENTHESIS_END:
                        yield from $this->spool($spool);
                        yield $token => $captured;
                        $this->position->leave();
                        break;

                    // Whitespace: has meaning if in tag and inline mode. In tag mode it indicates the beginning of
                    // an arguments list - in inline syntax it is used to determine if array syntax was used, or if a
                    // "pass" operator has whitespace around it.
                    case Splitter::BYTE_WHITESPACE_SPACE:
                    case Splitter::BYTE_WHITESPACE_TAB:
                    case Splitter::BYTE_WHITESPACE_RETURN:
                    case Splitter::BYTE_WHITESPACE_EOL:
                        if ($this->position->context->context === Context::CONTEXT_INLINE || $this->position->context->context === Context::CONTEXT_ARRAY) {
                            $spool[] = [$token, $captured];
                            #$this->position->switch($this->contexts->array);
                            /*
                            $last = end($spool);
                            if ($last !== false) {
                                $lastByte = ($last[0] ?? 0);
                                $spool[] = [$token, $captured];
                                if ($lastByte === Splitter::BYTE_SEPARATOR_COLON) {
                                    $this->position->switch($this->contexts->array);
                                } else {
                                    $this->position->switch($this->contexts->inline);
                                }
                            } else {
                                yield from $this->spool($spool);
                                yield $token => $captured;
                            }
                            */
                        } elseif ($this->position->context->context === Context::CONTEXT_TAG) {
                            yield $token => $captured;
                            $this->position->enter($this->contexts->attributes);
                        }
                        break;

                    case Splitter::BYTE_SEPARATOR_COLON:
                        if ($this->position->context->context === Context::CONTEXT_INLINE) {
                            if (is_numeric($captured->captured)) {
                                $this->position->switch($this->contexts->array);
                            }
                            $spool[] = [$token, $captured];
                        } elseif ($this->position->context->context === Context::CONTEXT_ARRAY) {
                            $spool[] = [$token, $captured];
                        } else {
                            yield $token => $captured;
                        }
                        break;

                    case Splitter::BYTE_SEPARATOR_COMMA:
                        $this->position->switch($this->contexts->array);
                        $spool[] = [$token, $captured];
                        break;

                    case Splitter::BYTE_SEPARATOR_EQUALS:
                        $spool[] = [$token, $captured];
                        break;

                    case Splitter::BYTE_PIPE:
                        $this->position->switch($this->contexts->inline);
                        yield from $this->spool($spool, [Context::CONTEXT_ARRAY => Context::CONTEXT_INLINE], [Splitter::BYTE_ARRAY_START => Splitter::BYTE_INLINE]);
                        yield $token => $captured;
                        break;

                    case Splitter::BYTE_PARENTHESIS_START:
                        $this->position->switch($this->contexts->inline);
                        yield from $this->spool($spool, [Context::CONTEXT_ARRAY => Context::CONTEXT_INLINE], [Splitter::BYTE_ARRAY_START => Splitter::BYTE_INLINE]);
                        #yield from $this->spool($spool);
                        yield $token => $captured;
                        $this->position->enter($this->contexts->array);
                        break;

                    case Splitter::BYTE_ARRAY_START:
                        yield from $this->spool($spool);
                        yield $token => $captured;
                        $this->position->enter($this->contexts->array);
                        break;

                    default:
                        throw new SequencingException('Unknown symbol: ' . addslashes(chr($token)));
                        break;

                }
            }

            yield from $this->spool($spool);

            if (!empty($this->position->stack)) {
                throw new SequencingException('The stack still contains ' . count($this->position->stack) . ' element(s)');
            }

        } catch (SequencingException $exception) {
            $character = (string) addslashes($this->source->source{$this->position->index});
            $ascii = (string) $this->source->bytes[$this->position->index];
            throw new SequencingException(
                sprintf(
                    $exception->getMessage(),
                    '"' . $character . '" (ASCII: ' . $ascii . ')'
                ),
                $exception->getCode()
            );
        }
    }

    protected function spool(iterable &$spool, array $contextMap = [], array $byteMap = []): \Generator
    {
        foreach ($spool as $set) {
            $set[1]->context->context = $contextMap[$set[1]->context->context] ?? $set[1]->context->context;
            yield ($byteMap[$set[0]] ?? $set[0]) => $set[1];
        }
        $spool = [];
    }
}
