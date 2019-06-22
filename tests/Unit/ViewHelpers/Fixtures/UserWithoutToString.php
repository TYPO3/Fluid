<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Dummy object to test Viewhelper behavior on objects without a __toString method
 */
class UserWithoutToString
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isNamed(): bool
    {
        return !empty($this->name);
    }

    /**
     * @return boolean
     */
    public function hasHasAccessor(): bool
    {
        return !empty($this->name);
    }

    /**
     * @return boolean
     */
    public function isIsAccessor(): bool
    {
        return !empty($this->name);
    }
}
