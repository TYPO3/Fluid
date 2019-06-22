<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class CountableIterator
 *
 * Fixture for an iterator with a count() method.
 */
class CountableIterator implements \Iterator, \Countable
{
    public function current(): void
    {
    }

    public function next(): void
    {
    }

    public function key(): void
    {
    }

    public function valid(): void
    {
    }

    public function rewind(): void
    {
    }

    public function count(): int
    {
    }
}
