<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TagBasedViewHelper
 */
class AbstractTagBasedViewHelperTest extends UnitTestCase
{
    public function setUp(): void
    {
        $this->viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, ['dummy'], [], '', false);
        $this->viewHelper->setRenderingContext(new RenderingContextFixture());
    }

    /**
     * @test
     */
    public function testConstructorSetsTagBuilder()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            true
        );
        self::assertAttributeInstanceOf(TagBuilder::class, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testSetTagBuilderSetsTagBuilder()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $tagBuilder = new TagBuilder('div');
        $viewHelper->setTagBuilder($tagBuilder);
        self::assertAttributeSame($tagBuilder, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testRenderCallsRenderOnTagBuilder()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $tagBuilder = $this->getMock(TagBuilder::class, ['render']);
        $tagBuilder->expects(self::once())->method('render')->willReturn('foobar');
        $viewHelper->setTagBuilder($tagBuilder);
        self::assertEquals('foobar', $viewHelper->render());
    }

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['registerArgument'],
            [],
            '',
            false
        );
        $viewHelper->expects(self::at(0))->method('registerArgument')->with('additionalAttributes');
        $viewHelper->expects(self::at(1))->method('registerArgument')->with('data');
        $viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function oneTagAttributeIsRenderedCorrectly()
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects(self::once())->method('addAttribute')->with('foo', 'bar');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['foo' => 'bar'];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function additionalTagAttributesAreRenderedCorrectly()
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects(self::once())->method('addAttribute')->with('foo', 'bar');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['additionalAttributes' => ['foo' => 'bar']];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function dataAttributesAreRenderedCorrectly()
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects(self::at(0))->method('addAttribute')->with('data-foo', 'bar');
        $mockTagBuilder->expects(self::at(1))->method('addAttribute')->with('data-baz', 'foos');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = ['data' => ['foo' => 'bar', 'baz' => 'foos']];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function ariaAttributesAreRenderedCorrectly()
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects(self::at(0))->method('addAttribute')->with('aria-foo', 'bar');
        $mockTagBuilder->expects(self::at(1))->method('addAttribute')->with('aria-baz', 'foos');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = ['aria' => ['foo' => 'bar', 'baz' => 'foos']];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function testValidateAdditionalArgumentsThrowsExceptionIfContainingNonDataArguments()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $this->setExpectedException(Exception::class);
        $viewHelper->validateAdditionalArguments(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testHandleAdditionalArgumentsSetsTagAttributesForDataArguments()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $tagBuilder = $this->getMock(TagBuilder::class, ['addAttribute']);
        $tagBuilder->expects(self::at(0))->method('addAttribute')->with('data-foo', 'foo');
        $tagBuilder->expects(self::at(1))->method('addAttribute')->with('data-bar', 'bar');
        $viewHelper->setTagBuilder($tagBuilder);
        $viewHelper->handleAdditionalArguments(['data-foo' => 'foo', 'data-bar' => 'bar']);
        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function testHandleAdditionalArgumentsSetsTagAttributesForAriaArguments()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $viewHelper->setRenderingContext(new RenderingContextFixture());
        $tagBuilder = $this->getMock(TagBuilder::class, ['addAttribute']);
        $tagBuilder->expects(self::at(0))->method('addAttribute')->with('aria-foo', 'foo');
        $tagBuilder->expects(self::at(1))->method('addAttribute')->with('aria-bar', 'bar');
        $viewHelper->setTagBuilder($tagBuilder);
        $viewHelper->handleAdditionalArguments(['aria-foo' => 'foo', 'aria-bar' => 'bar']);
        $viewHelper->initializeArgumentsAndRender();
    }

    /**
     * @test
     */
    public function standardTagAttributesAreRegistered()
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects(self::at(0))->method('addAttribute')->with('class', 'classAttribute');
        $mockTagBuilder->expects(self::at(1))->method('addAttribute')->with('dir', 'dirAttribute');
        $mockTagBuilder->expects(self::at(2))->method('addAttribute')->with('id', 'idAttribute');
        $mockTagBuilder->expects(self::at(3))->method('addAttribute')->with('lang', 'langAttribute');
        $mockTagBuilder->expects(self::at(4))->method('addAttribute')->with('style', 'styleAttribute');
        $mockTagBuilder->expects(self::at(5))->method('addAttribute')->with('title', 'titleAttribute');
        $mockTagBuilder->expects(self::at(6))->method('addAttribute')->with('accesskey', 'accesskeyAttribute');
        $mockTagBuilder->expects(self::at(7))->method('addAttribute')->with('tabindex', 'tabindexAttribute');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

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
        $this->viewHelper->_call('registerUniversalTagAttributes');
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initializeArguments();
        $this->viewHelper->initialize();
    }
}
