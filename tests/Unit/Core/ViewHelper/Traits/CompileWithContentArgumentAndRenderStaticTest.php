<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\TestViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class CompileWithContentArgumentAndRenderStaticTest
 */
class CompileWithContentArgumentAndRenderStaticTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testRenderCallsRenderStatic(): void
    {
        /** @var TestViewHelper|MockObject $instance */
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['getArguments', 'renderStatic'])->getMockForTrait();
        $instance->expects($this->atLeastOnce())->method('getArguments')->willReturn((new ArgumentCollection())->setRenderingContext(new RenderingContextFixture())->addDefinition(new ArgumentDefinition('foo', 'string', '', false)));
        $instance->render();
    }

    /**
     * @test
     */
    public function testGetContentArgumentNameThrowsExceptionIfNoArgumentsAvailable(): void
    {
        /** @var TestViewHelper|MockObject $instance */
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['getArguments'])->getMockForTrait();
        $instance->expects($this->once())->method('getArguments')->willReturn(new ArgumentCollection());
        $this->setExpectedException(Exception::class);
        $method = new \ReflectionMethod($instance, 'resolveContentArgumentName');
        $method->setAccessible(true);
        $method->invoke($instance);
    }

    /**
     * @test
     */
    public function builtClosureRendersChildrenWhenArgumentIsNotProvided(): void
    {
        /** @var TestViewHelper|MockObject $instance */
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['getArguments', 'renderChildren'])->getMockForTrait();
        $instance->expects($this->once())->method('getArguments')->willReturn((new ArgumentCollection())->addDefinition(new ArgumentDefinition('foo', 'string', '', false)));
        $method = new \ReflectionMethod($instance, 'buildRenderChildrenClosure');
        $method->setAccessible(true);
        $closure = $method->invoke($instance);
        $closure();
    }

    /**
     * @test
     */
    public function builtClosureRendersArgumentIfProvided(): void
    {
        $arguments = (new ArgumentCollection())->addDefinition(new ArgumentDefinition('foo', 'string', '', false));
        $arguments['foo'] = 'foo';
        /** @var TestViewHelper|MockObject $instance */
        $instance = $this->getMockBuilder(CompileWithContentArgumentAndRenderStatic::class)->setMethods(['getArguments', 'renderChildren'])->getMockForTrait();
        $instance->expects($this->once())->method('getArguments')->willReturn($arguments);
        $method = new \ReflectionMethod($instance, 'buildRenderChildrenClosure');
        $method->setAccessible(true);
        $closure = $method->invoke($instance);
        $closure();
    }
}
