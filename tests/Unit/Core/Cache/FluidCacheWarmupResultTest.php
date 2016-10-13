<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class FluidCacheWarmupResultTest
 */
class FluidCacheWarmupResultTest extends UnitTestCase
{

    /**
     * @param array $results
     * @param array $expected
     * @dataProvider getCacheWarmupResultTestValues
     * @test
     */
    public function testMerge(array $results, array $expected)
    {
        $result1 = $this->getAccessibleMock(FluidCacheWarmupResult::class, ['dummy']);
        $result1->_set('results', array_pop($results));
        $result2 = $this->getAccessibleMock(FluidCacheWarmupResult::class, ['dummy']);
        $result2->_set('results', array_pop($results));
        $result1->merge($result2);
        $this->assertAttributeSame($expected, 'results', $result1);
    }

    /**
     * @return array
     */
    public function getCacheWarmupResultTestValues()
    {
        return [
            [[['foo' => 'bar'], ['baz' => 'oof']], ['baz' => 'oof', 'foo' => 'bar']],
            [[['foo' => 'bar'], ['baz' => 'oof', 'foo' => 'baz']], ['baz' => 'oof', 'foo' => 'baz']],
        ];
    }

    /**
     * @test
     */
    public function testGetResults()
    {
        $subject = $this->getAccessibleMock(FluidCacheWarmupResult::class, ['dummy']);
        $subject->_set('results', ['foo' => 'bar']);
        $this->assertAttributeEquals(['foo' => 'bar'], 'results', $subject);
    }

    /**
     * @param ParsedTemplateInterface $subject
     * @param array $expected
     * @dataProvider getAddTestValues
     * @test
     */
    public function testAdd(ParsedTemplateInterface $subject, array $expected)
    {
        $result = new FluidCacheWarmupResult();
        $result->add($subject, 'foobar');
        $this->assertAttributeEquals(['foobar' => $expected], 'results', $result);
    }

    /**
     * @return array
     */
    public function getAddTestValues()
    {
        $subject1 = $this->getMockBuilder(
            ParsedTemplateInterface::class
        )->setMethods(
            ['isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier']
        )->getMockForAbstractClass();
        $subject1->expects($this->exactly(2))->method('isCompiled')->willReturn(false);
        $subject1->expects($this->once())->method('isCompilable')->willReturn(true);
        $subject1->expects($this->once())->method('hasLayout')->willReturn(false);
        $subject1->expects($this->once())->method('getIdentifier')->willReturn('subject1-identifier');
        $subject2 = $this->getMockBuilder(
            FailedCompilingState::class
        )->setMethods(
            ['isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier', 'getFailureReason', 'getMitigations']
        )->getMockForAbstractClass();
        $subject2->expects($this->exactly(2))->method('isCompiled')->willReturn(true);
        $subject2->expects($this->once())->method('isCompilable')->willReturn(true);
        $subject2->expects($this->once())->method('hasLayout')->willReturn(true);
        $subject2->expects($this->once())->method('getIdentifier')->willReturn('subject2-identifier');
        $subject2->expects($this->once())->method('getFailureReason')->willReturn('failure-reason');
        $subject2->expects($this->once())->method('getMitigations')->willReturn(['m1', 'm2']);
        return [
            [
                $subject1,
                [
                    FluidCacheWarmupResult::RESULT_COMPILABLE => true,
                    FluidCacheWarmupResult::RESULT_COMPILED => false,
                    FluidCacheWarmupResult::RESULT_HASLAYOUT => false,
                    FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject1-identifier'
                ]
            ],
            [
                $subject2,
                [
                    FluidCacheWarmupResult::RESULT_COMPILABLE => true,
                    FluidCacheWarmupResult::RESULT_COMPILED => true,
                    FluidCacheWarmupResult::RESULT_HASLAYOUT => true,
                    FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject2-identifier',
                    FluidCacheWarmupResult::RESULT_FAILURE => 'failure-reason',
                    FluidCacheWarmupResult::RESULT_MITIGATIONS => ['m1', 'm2']
                ]
            ],
        ];
    }
}
