<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;

/**
 * Fixture for a Component which allows naming the component
 * through constructor argument, and which does not implement
 * any special interfaces.
 */
class ComponentFixture extends AbstractComponent
{
    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }
}