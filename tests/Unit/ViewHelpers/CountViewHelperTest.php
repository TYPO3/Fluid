<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CountViewHelper;

/**
 * Testcase for CountViewHelper
 */
class CountViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var CountViewHelper
     */
    protected $viewHelper;

    public function setUp()
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(CountViewHelper::class, ['renderChildren']);
    }

    /**
     * @param mixed $subject
     * @param int $expectedResult
     * @dataProvider getCountTestValues
     */
    public function testCountOperation($subject, $expectedResult)
    {
        $actualResult = CountViewHelper::renderStatic(['subject' => $subject], function() use ($subject) { return $subject; }, $this->renderingContext);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function getCountTestValues()
    {
        return [
            [
                ['foo', 'bar', 'Baz'],
                3,
            ],
            [
                new \ArrayObject(['foo', 'bar']),
                2,
            ],
            [
                [],
                0,
            ]
        ];
    }

    /**
     * @test
     */
    public function renderThrowsExceptionIfGivenSubjectIsNotCountable()
    {
        $this->setExpectedException(Exception::class);
        CountViewHelper::renderStatic([], function() { return new \stdClass(); }, $this->renderingContext);
    }
}
