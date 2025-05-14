<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler\Fixtures\AbstractCompiledTemplateTestFixture;

final class AbstractCompiledTemplateTest extends TestCase
{
    #[Test]
    public function setIdentifierDoesNotChangeObject(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        $before = clone $subject;
        $subject->setIdentifier('test');
        self::assertEquals($before, $subject);
    }

    #[Test]
    public function getIdentifierReturnsClassName(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals($subject->getIdentifier(), get_class($subject));
    }

    #[Test]
    public function getVariableContainerReturnsStandardVariableProvider(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertInstanceOf(StandardVariableProvider::class, $subject->getVariableContainer());
    }

    #[Test]
    public function renderReturnsEmptyString(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals('', $subject->render(new RenderingContext()));
    }

    #[Test]
    public function getLayoutNameReturnsEmptyString(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals('', $subject->getLayoutName(new RenderingContext()));
    }

    #[Test]
    public function hasLayoutReturnsFalse(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertFalse($subject->hasLayout());
    }

    #[Test]
    public function isCompilableReturnsFalse(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertFalse($subject->isCompilable());
    }

    #[Test]
    public function isCompiledReturnsTrue(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertTrue($subject->isCompiled());
    }
}
