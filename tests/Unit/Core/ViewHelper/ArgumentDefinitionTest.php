<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class ArgumentDefinitionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function objectStoresDataCorrectly(): void
    {
        $name = 'This is a name';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;
        $isMethodParameter = true;
        $argumentDefinition = new ArgumentDefinition($name, $type, $description, $isRequired, null);

        self::assertEquals($argumentDefinition->getName(), $name, 'Name could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getDescription(), $description, 'Description could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getType(), $type, 'Type could not be retrieved correctly');
        self::assertEquals($argumentDefinition->isRequired(), $isRequired, 'Required flag could not be retrieved correctly.');
    }
}
