<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

class Context
{
    public const CONTEXT_ROOT = 0;
    public const CONTEXT_INLINE = 1;
    public const CONTEXT_TAG = 2;
    public const CONTEXT_ARRAY= 3;
    public const CONTEXT_QUOTED = 4;
    public const CONTEXT_ATTRIBUTES = 5;
    public const CONTEXT_DEAD = 6;

    public $context = self::CONTEXT_ROOT;
    public $primaryMask = 0;
    public $secondaryMask = 0;
    public $startingByte = 0;

    public function __construct(int $context, string $tokens)
    {
        $this->context = $context;
        foreach (str_split($tokens) as $character) {
            $byte = ord($character);
            if ($byte >= 64) {
                $this->secondaryMask |= (1 << ($byte - Splitter::MAP_SHIFT));
            } elseif ($byte < 64) {
                $this->primaryMask |= (1 << $byte);
            }
        }
    }
}
