<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class ArrayAccessDummy
 */
class ArrayAccessDummy implements \ArrayAccess
{
    protected $array = [];

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }

    public function getProperty(): string
    {
        return 'foobar';
    }
}
