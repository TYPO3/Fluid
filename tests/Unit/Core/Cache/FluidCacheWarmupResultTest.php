<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheWarmupResult;
use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;

final class FluidCacheWarmupResultTest extends TestCase
{
    #[Test]
    public function mergeCombinesTwoResults(): void
    {
        $result1 = new FluidCacheWarmupResult();
        $result1->add($this->createMock(ParsedTemplateInterface::class), 'baz');
        $result2 = new FluidCacheWarmupResult();
        $result2->add($this->createMock(ParsedTemplateInterface::class), 'foo');
        $subject = new FluidCacheWarmupResult();
        $subject->merge($result1);
        $subject->merge($result2);
        $expected = [
            'baz' => [
                'compilable' => false,
                'compiled' => null,
                'hasLayout' => null,
                'compiledClassName' => null,
            ],
            'foo' => [
                'compilable' => false,
                'compiled' => null,
                'hasLayout' => null,
                'compiledClassName' => null,
            ],
        ];
        self::assertSame($expected, $subject->getResults());
    }

    #[Test]
    public function mergeOverridesExistingResult(): void
    {
        $result1 = new FluidCacheWarmupResult();
        $result1->add($this->createMock(ParsedTemplateInterface::class), 'baz');
        $result2 = new FluidCacheWarmupResult();
        $result2->add($this->createMock(ParsedTemplateInterface::class), 'baz');
        $subject = new FluidCacheWarmupResult();
        $subject->merge($result1);
        $subject->merge($result2);
        $expected = [
            'baz' => [
                'compilable' => false,
                'compiled' => null,
                'hasLayout' => null,
                'compiledClassName' => null,
            ],
        ];
        self::assertSame($expected, $subject->getResults());
    }

    #[Test]
    public function addWorksWithParsedTemplate(): void
    {
        $parsedTemplateMock = $this->createMock(ParsedTemplateInterface::class);
        $parsedTemplateMock->expects(self::once())->method('isCompiled')->willReturn(false);
        $parsedTemplateMock->expects(self::once())->method('isCompilable')->willReturn(true);
        $parsedTemplateMock->expects(self::once())->method('hasLayout')->willReturn(false);
        $parsedTemplateMock->expects(self::once())->method('getIdentifier')->willReturn('subject1-identifier');
        $subject = new FluidCacheWarmupResult();
        $subject->add($parsedTemplateMock, 'foobar');
        $expected = [
            'foobar' => [
                FluidCacheWarmupResult::RESULT_COMPILABLE => true,
                FluidCacheWarmupResult::RESULT_COMPILED => false,
                FluidCacheWarmupResult::RESULT_HASLAYOUT => false,
                FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject1-identifier',
            ],
        ];
        self::assertSame($expected, $subject->getResults());
    }

    #[Test]
    public function addWorksWithFailedCompilingState(): void
    {
        $failedCompilingStateMock = $this->createMock(FailedCompilingState::class);
        $failedCompilingStateMock->expects(self::once())->method('isCompiled')->willReturn(true);
        $failedCompilingStateMock->expects(self::never())->method('isCompilable');
        $failedCompilingStateMock->expects(self::once())->method('hasLayout')->willReturn(true);
        $failedCompilingStateMock->expects(self::once())->method('getIdentifier')->willReturn('subject2-identifier');
        $failedCompilingStateMock->expects(self::once())->method('getFailureReason')->willReturn('failure-reason');
        $failedCompilingStateMock->expects(self::once())->method('getMitigations')->willReturn(['m1', 'm2']);
        $expected = [
            'foobar' => [
                FluidCacheWarmupResult::RESULT_COMPILABLE => true,
                FluidCacheWarmupResult::RESULT_COMPILED => true,
                FluidCacheWarmupResult::RESULT_HASLAYOUT => true,
                FluidCacheWarmupResult::RESULT_COMPILEDCLASS => 'subject2-identifier',
                FluidCacheWarmupResult::RESULT_FAILURE => 'failure-reason',
                FluidCacheWarmupResult::RESULT_MITIGATIONS => ['m1', 'm2'],
            ],
        ];
        $subject = new FluidCacheWarmupResult();
        $subject->add($failedCompilingStateMock, 'foobar');
        self::assertSame($expected, $subject->getResults());
    }
}
