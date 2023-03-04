<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class FailedCompilingStateTest extends UnitTestCase
{
    public static function getPropertyTestValues(): array
    {
        return [
            ['failureReason', 'test reason'],
            ['mitigations', ['m1', 'm2']]
        ];
    }

    /**
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testGetter(string $property, $value): void
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject->_set($property, $value);
        $method = 'get' . ucfirst($property);
        self::assertEquals($value, $subject->$method());
    }

    /**
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testSetter(string $property, $value): void
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject->_set($property, $value);
        $method = 'set' . ucfirst($property);
        $getter = 'get' . ucfirst($property);
        $subject->$method($value);
        self::assertEquals($value, $subject->$getter());
    }

    /**
     * @test
     */
    public function testAddMitigation(): void
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject->_set('mitigations', ['m1']);
        $subject->addMitigation('m2');
        self::assertEquals(['m1', 'm2'], $subject->getMitigations());
    }
}
