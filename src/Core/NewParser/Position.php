<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

class Position
{
    // Crazy enough, the output of "unpack()" is indexed starting from 1, not 0.
    public $index = 1;
    public $lastYield = 0;

    /** @var Context */
    public $context;

    public $stack = [];

    public function __construct(Context $context, int $lastYield = 0, int $index = 1)
    {
        $this->context = $context;
        $this->lastYield = $lastYield;
        $this->index = $index;
    }

    public function enter(Context $context, int $startingByte = 0): self
    {
        #var_dump('Entering: ' . $this->context->context);
        $clone = clone $this->context;;
        $clone->startingByte = $startingByte;
        $this->stack[] = $clone;
        $this->context = $context;
        return $this;
    }

    public function switch(Context $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function leave(): self
    {
        #var_dump('Leaving: ' . $this->context->context);
        $this->context = array_pop($this->stack) ?: $this->context;
        return $this;
    }

    public function copy(): self
    {
        return new self($this->context, $this->lastYield, $this->index);
    }

    public function byteMatchesStartingByteOfTopmostStackElement(int $byte): bool
    {
        return end($this->stack)->startingByte === $byte;
    }

    public function pad(int $before, int $after): self
    {
        $clone = clone $this;
        $clone->lastYield -= ($before + 1); // Note: packing sequences always starts at +1 so we must add this padding.
        $clone->index += $after;
        return $clone;
    }

    public function error(): void
    {
        throw new \RuntimeException('Unknown symbol %s encountered at index ' . $this->index . ' in context ' . $this->getContextName());
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
