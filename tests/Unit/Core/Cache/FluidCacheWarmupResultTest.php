<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class FluidCacheWarmupResultTest extends UnitTestCase
{
    public static function getCacheWarmupResultTestValues(): array
    {
        return [
            [[['foo' => 'bar'], ['baz' => 'oof']], ['baz' => 'oof', 'foo' => 'bar']],
            [[['foo' => 'bar'], ['baz' => 'oof', 'foo' => 'baz']], ['baz' => 'oof', 'foo' => 'baz']],
        ];
    }

    /**
     * @dataProvider getCacheWarmupResultTestValues
     * @test
     */
    public function testMerge(array $results, array $expected): void
    {
        $result1 = $this->getAccessibleMock(FluidCacheWarmupResult::class, []);
        $result1->_set('results', array_pop($results));
        $result2 = $this->getAccessibleMock(FluidCacheWarmupResult::class, []);
        $result2->_set('results', array_pop($results));
        $result1->merge($result2);
        self::assertEquals($expected, $result1->getResults());
    }

    /**
     * @test
     */
    public function testGetResults(): void
    {
        $subject = $this->getAccessibleMock(FluidCacheWarmupResult::class, []);
        $subject->_set('results', ['foo' => 'bar']);
        self::assertEquals(['foo' => 'bar'], $subject->getResults());
    }

    /**
     * @test
     */
    public function addWorksWithParsedTemplate(): void
    {
        $subject = $this->getMockBuilder(ParsedTemplateInterface::class)
            ->onlyMethods(
                ['isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier']
            )
            ->getMockForAbstractClass();
        $subject->expects(self::once())->method('isCompiled')->willReturn(false);
        $subject->expects(self::once())->method('isCompilable')->willReturn(true);
        $subject->expects(self::once())->method('hasLayout')->willReturn(false);
        $subject->expects(self::once())->method('getIdentifier')->willReturn('subject1-identifier');
        $expected = [
            FluidCacheWarmupResult::RESULT_COMPILABLE => true,
            FluidCacheWarmupResult::RESULT_COMPILED => false,
            FluidCacheWarmupResult::RESULT_HASLAYOUT => false,
            FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject1-identifier'
        ];
        $result = new FluidCacheWarmupResult();
        $result->add($subject, 'foobar');
        self::assertEquals(['foobar' => $expected], $result->getResults());
    }

    /**
     * @test
     */
    public function addWorksWithFailedCompilingState(): void
    {
        $subject = $this->getMockBuilder(FailedCompilingState::class)
            ->onlyMethods(
                ['isCompiled', 'isCompilable', 'hasLayout', 'getIdentifier', 'getFailureReason', 'getMitigations']
            )
            ->getMockForAbstractClass();
        $subject->expects(self::once())->method('isCompiled')->willReturn(true);
        $subject->expects(self::never())->method('isCompilable');
        $subject->expects(self::once())->method('hasLayout')->willReturn(true);
        $subject->expects(self::once())->method('getIdentifier')->willReturn('subject2-identifier');
        $subject->expects(self::once())->method('getFailureReason')->willReturn('failure-reason');
        $subject->expects(self::once())->method('getMitigations')->willReturn(['m1', 'm2']);
        $expected = [
            FluidCacheWarmupResult::RESULT_COMPILABLE => true,
            FluidCacheWarmupResult::RESULT_COMPILED => true,
            FluidCacheWarmupResult::RESULT_HASLAYOUT => true,
            FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject2-identifier',
            FluidCacheWarmupResult::RESULT_FAILURE => 'failure-reason',
            FluidCacheWarmupResult::RESULT_MITIGATIONS => ['m1', 'm2']
        ];
        $result = new FluidCacheWarmupResult();
        $result->add($subject, 'foobar');
        self::assertEquals(['foobar' => $expected], $result->getResults());
    }
}
