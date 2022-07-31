<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/**
 * Class CountableIterator
 *
 * Fixture for an iterator with a count() method.
 */
class CountableIterator implements \Iterator, \Countable
{
    #[\ReturnTypeWillChange]
    public function current()
    {
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return false;
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return 0;
    }
}
