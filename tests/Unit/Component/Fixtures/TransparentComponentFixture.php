<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\TransparentComponentInterface;

/**
 * Fixture for a Component which allows child components
 * to be resolved recursively.
 */
class TransparentComponentFixture extends ComponentFixture implements TransparentComponentInterface
{
}