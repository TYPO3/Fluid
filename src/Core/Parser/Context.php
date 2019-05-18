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

    public function __construct(int $context, int $primaryMask, int $secondaryMask)
    {
        $this->context = $context;
        $this->primaryMask = $primaryMask;
        $this->secondaryMask = $secondaryMask;
    }
}
