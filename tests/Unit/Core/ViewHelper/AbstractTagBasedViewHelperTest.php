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

    /**
     * @var MockObject|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed|mixed
     */
    public $viewHelper;
    public function setUp(): void
    {
        $this->viewHelper = $this->getAccessibleMock(AbstractTagBasedViewHelper::class, ['dummy'], [], '', false);
        $this->viewHelper->setRenderingContext(new RenderingContextFixture());
    }

    /**
     * @test
     */
    public function testConstructorSetsTagBuilder(): void
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy']
        );
        $this->assertAttributeInstanceOf(TagBuilder::class, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testSetTagBuilderSetsTagBuilder(): void
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
        $this->assertAttributeSame($tagBuilder, 'tag', $viewHelper);
    }

    /**
     * @test
     */
    public function testRenderCallsRenderOnTagBuilder(): void
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $tagBuilder = $this->getMock(TagBuilder::class, ['render']);
        $tagBuilder->expects($this->once())->method('render')->willReturn('foobar');
        $viewHelper->setTagBuilder($tagBuilder);
        $this->assertEquals('foobar', $viewHelper->render());
    }

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments(): void
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractTagBasedViewHelper::class,
            ['registerArgument'],
            [],
            '',
            false
        );
        $viewHelper->expects($this->at(0))->method('registerArgument')->with('additionalAttributes');
        $viewHelper->expects($this->at(1))->method('registerArgument')->with('data');
        $viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function oneTagAttributeIsRenderedCorrectly(): void
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['foo' => 'bar'];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function additionalTagAttributesAreRenderedCorrectly(): void
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects($this->once())->method('addAttribute')->with('foo', 'bar');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $this->viewHelper->_call('registerTagAttribute', 'foo', 'string', 'Description', false);
        $arguments = ['additionalAttributes' => ['foo' => 'bar']];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function dataAttributesAreRenderedCorrectly(): void
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('data-foo', 'bar');
        $mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('data-baz', 'foos');
        $this->viewHelper->setTagBuilder($mockTagBuilder);

        $arguments = ['data' => ['foo' => 'bar', 'baz' => 'foos']];
        $this->viewHelper->setArguments($arguments);
        $this->viewHelper->initialize();
    }

    /**
     * @test
     */
    public function testValidateAdditionalArgumentsThrowsExceptionIfContainingNonDataArguments(): void
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
    public function testHandleAdditionalArgumentsSetsTagAttributesForDataArguments(): void
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
        $tagBuilder->expects($this->at(0))->method('addAttribute')->with('data-foo', 'foo');
        $tagBuilder->expects($this->at(1))->method('addAttribute')->with('data-bar', 'bar');
        $viewHelper->setTagBuilder($tagBuilder);
        $viewHelper->handleAdditionalArguments(['data-foo' => 'foo', 'data-bar' => 'bar']);
    }

    /**
     * @test
     */
    public function standardTagAttributesAreRegistered(): void
    {
        $mockTagBuilder = $this->getMock(TagBuilder::class, ['addAttribute'], [], '', false);
        $mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('class', 'classAttribute');
        $mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('dir', 'dirAttribute');
        $mockTagBuilder->expects($this->at(2))->method('addAttribute')->with('id', 'idAttribute');
        $mockTagBuilder->expects($this->at(3))->method('addAttribute')->with('lang', 'langAttribute');
        $mockTagBuilder->expects($this->at(4))->method('addAttribute')->with('style', 'styleAttribute');
        $mockTagBuilder->expects($this->at(5))->method('addAttribute')->with('title', 'titleAttribute');
        $mockTagBuilder->expects($this->at(6))->method('addAttribute')->with('accesskey', 'accesskeyAttribute');
        $mockTagBuilder->expects($this->at(7))->method('addAttribute')->with('tabindex', 'tabindexAttribute');
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
