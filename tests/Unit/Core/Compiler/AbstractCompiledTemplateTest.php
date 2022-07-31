<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class AbstractCompiledTemplateTest
 */
class AbstractCompiledTemplateTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testSetIdentifierDoesNotChangeObject()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $before = clone $instance;
        $instance->setIdentifier('test');
        self::assertEquals($before, $instance);
    }

    /**
     * @test
     */
    public function testGetIdentifierReturnsClassName()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        self::assertEquals($instance->getIdentifier(), get_class($instance));
    }

    /**
     * @test
     */
    public function testParentGetVariableContainerMethodReturnsStandardVariableProvider()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->getVariableContainer();
        self::assertInstanceOf(StandardVariableProvider::class, $result);
    }

    /**
     * @test
     */
    public function testParentRenderMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->render(new RenderingContextFixture());
        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testParentGetLayoutNameMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->getLayoutName(new RenderingContextFixture());
        self::assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testParentHasLayoutMethodReturnsFalse()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->hasLayout();
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testIsCompilableReturnsFalse()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->isCompilable();
        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function testIsCompiledReturnsTrue()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->isCompiled();
        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function testAddCompiledNamespacesDoesNothing()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $context = new RenderingContextFixture();
        $before = $context->getViewHelperResolver()->getNamespaces();
        $instance->addCompiledNamespaces($context);
        $after = $context->getViewHelperResolver()->getNamespaces();
        self::assertEquals($before, $after);
    }
}
