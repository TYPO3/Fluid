<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class AbstractTagBasedViewHelperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testConstructorSetsTagBuilder(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], []);
        self::assertAttributeInstanceOf(TagBuilder::class, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testSetTagBuilderSetsTagBuilder(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $tagBuilder = new TagBuilder('div');
        $viewHelper->setTagBuilder($tagBuilder);
        self::assertAttributeSame($tagBuilder, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testRenderCallsRenderOnTagBuilder(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $tagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['render'])->getMock();
        $tagBuilder->expects(self::once())->method('render')->willReturn('foobar');
        $viewHelper->setTagBuilder($tagBuilder);
        self::assertEquals('foobar', $viewHelper->render());
    }

    /**
     * @test
     */
    public function oneTagAttributeIsRenderedCorrectly(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());

        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $mockTagBuilder->expects(self::once())->method('addAttribute')->with('foo', 'bar');
        $viewHelper->setTagBuilder($mockTagBuilder);

        $viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['foo' => 'bar'];
        $viewHelper->setArguments($arguments);
        $viewHelper->initialize();
    }

    /**
     * @test
     */
    public function additionalTagAttributesAreRenderedCorrectly(): void
    {
        $subject = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $subject->setRenderingContext(new RenderingContextFixture());

        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $mockTagBuilder->expects(self::once())->method('addAttribute')->with('foo', 'bar');
        $subject->setTagBuilder($mockTagBuilder);

        $subject->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['additionalAttributes' => ['foo' => 'bar']];
        $subject->setArguments($arguments);
        $subject->initialize();
    }

    /**
     * @test
     */
    public function dataAttributesAreRenderedCorrectly(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());

        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $series = [
            ['data-foo', 'fooValue'],
            ['data-bar', 'barValue'],
        ];
        $mockTagBuilder->expects(self::atLeastOnce())->method('addAttribute')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = ['data' => ['foo' => 'fooValue', 'bar' => 'barValue']];
        $viewHelper->setArguments($arguments);
        $viewHelper->initialize();
    }

    /**
     * @test
     */
    public function ariaAttributesAreRenderedCorrectly(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());

        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $series = [
            ['aria-foo', 'fooValue'],
            ['aria-bar', 'barValue'],
        ];
        $mockTagBuilder->expects(self::atLeastOnce())->method('addAttribute')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = ['aria' => ['foo' => 'fooValue', 'bar' => 'barValue']];
        $viewHelper->setArguments($arguments);
        $viewHelper->initialize();
    }

    /**
     * @test
     */
    public function testValidateAdditionalArgumentsThrowsExceptionIfContainingNonDataArguments(): void
    {
        $this->expectException(Exception::class);
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $viewHelper->validateAdditionalArguments(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testHandleAdditionalArgumentsSetsTagAttributesForDataArguments(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $tagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $series = [
            ['data-foo', 'fooValue'],
            ['data-bar', 'barValue'],
        ];
        $tagBuilder->expects(self::atLeastOnce())->method('addAttribute')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $viewHelper->setTagBuilder($tagBuilder);
        $viewHelper->handleAdditionalArguments(['data-foo' => 'fooValue', 'data-bar' => 'barValue']);
        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function testHandleAdditionalArgumentsSetsTagAttributesForAriaArguments(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $tagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $series = [
            ['aria-foo', 'fooValue'],
            ['aria-bar', 'barValue'],
        ];
        $tagBuilder->expects(self::atLeastOnce())->method('addAttribute')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $viewHelper->setTagBuilder($tagBuilder);
        $viewHelper->handleAdditionalArguments(['aria-foo' => 'fooValue', 'aria-bar' => 'barValue']);
        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function standardTagAttributesAreRegistered(): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, [], [], '', false);
        $viewHelper->setRenderingContext(new RenderingContextFixture());

        $mockTagBuilder = $this->getMockBuilder(TagBuilder::class)->onlyMethods(['addAttribute'])->getMock();
        $series = [
            ['class', 'classAttribute'],
            ['dir', 'dirAttribute'],
            ['id', 'idAttribute'],
            ['lang', 'langAttribute'],
            ['style', 'styleAttribute'],
            ['title', 'titleAttribute'],
            ['accesskey', 'accesskeyAttribute'],
            ['tabindex', 'tabindexAttribute']
        ];
        $mockTagBuilder->expects(self::atLeastOnce())->method('addAttribute')->willReturnCallback(function (...$args) use (&$series): void {
            [$expectedArgOne, $expectedArgTwo] = array_shift($series);
            self::assertSame($expectedArgOne, $args[0]);
            self::assertSame($expectedArgTwo, $args[1]);
        });
        $viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = [
            'class' => 'classAttribute',
            'dir' => 'dirAttribute',
            'id' => 'idAttribute',
            'lang' => 'langAttribute',
            'style' => 'styleAttribute',
            'title' => 'titleAttribute',
            'accesskey' => 'accesskeyAttribute',
            'tabindex' => 'tabindexAttribute'
        ];
        $viewHelper->_call('registerUniversalTagAttributes');
        $viewHelper->setArguments($arguments);
        $viewHelper->initializeArguments();
        $viewHelper->initialize();
    }
}
