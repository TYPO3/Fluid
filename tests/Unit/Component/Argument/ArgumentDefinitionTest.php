<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for \TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition
 */
class ArgumentDefinitionTest extends UnitTestCase
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
        $argumentDefinition = new ArgumentDefinition($name, $type, $description, $isRequired, null);

        $this->assertEquals($argumentDefinition->getName(), $name, 'Name could not be retrieved correctly.');
        $this->assertEquals($argumentDefinition->getDescription(), $description, 'Description could not be retrieved correctly.');
        $this->assertEquals($argumentDefinition->getType(), $type, 'Type could not be retrieved correctly');
        $this->assertEquals($argumentDefinition->isRequired(), $isRequired, 'Required flag could not be retrieved correctly.');
    }
}
