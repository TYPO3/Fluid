<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Test cases for the FluidRenderer API
 */
class FluidRendererTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testRenderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknown(): void
    {
        $parsedTemplate = $this->getMockBuilder(ComponentInterface::class)->setMethods(['getNamedChild'])->getMockForAbstractClass();
        $parsedTemplate->expects($this->any())->method('getNamedChild')->willThrowException(new ChildNotFoundException('...'));
        $context = new RenderingContextFixture();
        $subject = $this->getMockBuilder(FluidRenderer::class)
            ->setMethods(['getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext'])
            ->setConstructorArgs([$context])
            ->enableOriginalConstructor()
            ->getMock();
        $subject->expects($this->once())->method('getCurrentRenderingContext')->willReturn($context);
        $subject->expects($this->once())->method('getCurrentRenderingType')->willReturn(FluidRenderer::RENDERING_LAYOUT);
        $subject->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $this->setExpectedException(ChildNotFoundException::class);
        $subject->renderSection('Missing');
    }
}