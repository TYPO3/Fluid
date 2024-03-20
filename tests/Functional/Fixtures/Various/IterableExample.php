<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

class IterableExample implements \Iterator
{
    public function __construct(private array $data) {}

    public function current(): mixed
    {
        return current($this->data);
    }

    public function key(): mixed
    {
        return key($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function valid(): bool
    {
        return key($this->data) !== null;
    }
}
