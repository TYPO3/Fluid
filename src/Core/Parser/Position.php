<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

class Position
{
    // Crazy enough, the output of "unpack()" is indexed starting from 1, not 0.
    public $index = 1;
    public $lastYield = 0;

    /** @var string|null */
    public $captured = null;

    /** @var Context */
    public $context;

    public function __construct(Context $context, int $lastYield = 0, int $index = 1, ?string $captured = null)
    {
        $this->context = $context;
        $this->lastYield = $lastYield;
        $this->index = $index;
        $this->captured = $captured;
    }

    public function copy(?string &$captured): self
    {
        $copy = new self($this->context, $this->lastYield, $this->index, $captured);
        $captured = null;
        return $copy;
    }

    public function pad(int $before, int $after): self
    {
        $clone = clone $this;
        $clone->lastYield -= ($before + 1); // Note: packing sequences always starts at +1 so we must add this padding.
        $clone->index += $after;
        return $clone;
    }

    public function getContextName(): string
    {
        static $consts = [];
        if (empty($consts)) {
            $reflect = new \ReflectionClass(Context::class);
            $consts = array_flip($reflect->getConstants());
        }
        return $consts[$this->context->context] ?? 'UNKNOWN';
    }
}
