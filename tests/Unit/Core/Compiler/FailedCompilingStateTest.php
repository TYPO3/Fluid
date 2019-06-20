<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
        $subject = $this->getAccessibleMock(FailedCompilingState::class, ['dummy']);
        $subject->_set($property, $value);
        $method = 'get' . ucfirst($property);
        $this->assertEquals($value, $subject->$method());
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testSetter($property, $value)
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, ['dummy']);
        $subject->_set($property, $value);
        $method = 'set' . ucfirst($property);
        $getter = 'get' . ucfirst($property);
        $subject->$method($value);
        $this->assertEquals($value, $subject->$getter());
    }

    /**
     * @test
     */
    public function testAddMitigation()
    {
        $subject = $this->getAccessibleMock(FailedCompilingState::class, ['dummy']);
        $subject->_set('mitigations', ['m1']);
        $subject->addMitigation('m2');
        $this->assertEquals(['m1', 'm2'], $subject->getMitigations());
    }

    /**
     * @return array
     */
    public function getPropertyTestValues()
    {
        return [
            ['failureReason', 'test reason'],
            ['mitigations', ['m1', 'm2']]
        ];
    }
}
