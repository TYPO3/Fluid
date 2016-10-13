<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class StopCompilingChildrenExceptionTest
 */
class StopCompilingChildrenExceptionTest extends UnitTestCase
{

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyTestValues
     * @test
     */
    public function testGetter($property, $value)
    {
        $subject = $this->getAccessibleMock(StopCompilingChildrenException::class, ['dummy']);
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
        $subject = $this->getAccessibleMock(StopCompilingChildrenException::class, ['dummy']);
        $subject->_set($property, $value);
        $method = 'set' . ucfirst($property);
        $subject->$method($value);
        $this->assertAttributeEquals($value, $property, $subject);
    }

    /**
     * @return array
     */
    public function getPropertyTestValues()
    {
        return [
            ['replacementString', 'test replacement string'],
        ];
    }
}
