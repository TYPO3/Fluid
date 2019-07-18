<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Context collection used by Sequencer when splitting templates.
 */
class Context
{
    public const CONTEXT_ROOT = 0;
    public const CONTEXT_INLINE = 1;
    public const CONTEXT_TAG = 2;
    public const CONTEXT_ARRAY= 3;
    public const CONTEXT_QUOTED = 4;
    public const CONTEXT_ATTRIBUTES = 5;
    public const CONTEXT_DEAD = 6;
    public const CONTEXT_PROTECTED = 7;
    public const CONTEXT_ACCESSOR = 8;
    public const CONTEXT_DATA = 9;
    public const CONTEXT_TOGGLE = 10;
    public const CONTEXT_EMPTY = 11;

    public $context = self::CONTEXT_ROOT;
    public $primaryMask = 0;
    public $secondaryMask = 0;
    public $bytes = [];

    public function __construct(int $context, string $tokens)
    {
        $this->context = $context;
        foreach (unpack('C*', $tokens) as $byte) {
            $this->bytes[] = $byte;
            if ($byte >= 64) {
                $this->secondaryMask |= (1 << ($byte - Splitter::MAP_SHIFT));
            } elseif ($byte < 64) {
                $this->primaryMask |= (1 << $byte);
            }
        }
    }
}
