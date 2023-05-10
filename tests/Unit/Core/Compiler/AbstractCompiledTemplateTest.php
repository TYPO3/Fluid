<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler\Fixtures\AbstractCompiledTemplateTestFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractCompiledTemplateTest extends UnitTestCase
{
    /**
     * @test
     */
    public function setIdentifierDoesNotChangeObject(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        $before = clone $subject;
        $subject->setIdentifier('test');
        self::assertEquals($before, $subject);
    }

    /**
     * @test
     */
    public function getIdentifierReturnsClassName(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals($subject->getIdentifier(), get_class($subject));
    }

    /**
     * @test
     */
    public function getVariableContainerReturnsStandardVariableProvider(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertInstanceOf(StandardVariableProvider::class, $subject->getVariableContainer());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyString(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals('', $subject->render(new RenderingContext()));
    }

    /**
     * @test
     */
    public function getLayoutNameReturnsEmptyString(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertEquals('', $subject->getLayoutName(new RenderingContext()));
    }

    /**
     * @test
     */
    public function hasLayoutReturnsFalse(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertFalse($subject->hasLayout());
    }

    /**
     * @test
     */
    public function isCompilableReturnsFalse(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertFalse($subject->isCompilable());
    }

    /**
     * @test
     */
    public function isCompiledReturnsTrue(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        self::assertTrue($subject->isCompiled());
    }

    /**
     * @test
     */
    public function addCompiledNamespacesDoesNothing(): void
    {
        $subject = new AbstractCompiledTemplateTestFixture();
        $context = new RenderingContext();
        $before = $context->getViewHelperResolver()->getNamespaces();
        $subject->addCompiledNamespaces($context);
        $after = $context->getViewHelperResolver()->getNamespaces();
        self::assertEquals($before, $after);
    }
}
