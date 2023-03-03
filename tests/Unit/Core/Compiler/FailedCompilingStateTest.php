<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class FailedCompilingStateTest
 */
class FailedCompilingStateTest extends UnitTestCase
{

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testGetter($property, $value)
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject->_set($property, $value);
        $method = 'get' . ucfirst($property);
        self::assertEquals($value, $subject->$method());
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testSetter($property, $value)
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
    public function testAddMitigation()
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, []);
        $subject->_set('mitigations', ['m1']);
        $subject->addMitigation('m2');
        self::assertEquals(['m1', 'm2'], $subject->getMitigations());
    }

    /**
     * @return array
     */
    public static function getPropertyTestValues()
    {
        return [
            ['failureReason', 'test reason'],
            ['mitigations', ['m1', 'm2']]
        ];
    }
}
