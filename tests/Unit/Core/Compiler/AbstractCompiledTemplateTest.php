<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
        $this->assertEquals($before, $instance);
    }

    /**
     * @test
     */
    public function testGetIdentifierReturnsClassName()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $this->assertEquals($instance->getIdentifier(), get_class($instance));
    }

    /**
     * @test
     */
    public function testParentGetVariableContainerMethodReturnsStandardVariableProvider()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->getVariableContainer();
        $this->assertInstanceOf(StandardVariableProvider::class, $result);
    }

    /**
     * @test
     */
    public function testParentRenderMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->render(new RenderingContextFixture());
        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testParentGetLayoutNameMethodReturnsEmptyString()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->getLayoutName(new RenderingContextFixture());
        $this->assertEquals('', $result);
    }

    /**
     * @test
     */
    public function testParentHasLayoutMethodReturnsFalse()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->hasLayout();
        $this->assertEquals(false, $result);
    }

    /**
     * @test
     */
    public function testIsCompilableReturnsFalse()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->isCompilable();
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function testIsCompiledReturnsTrue()
    {
        $instance = $this->getMockForAbstractClass(AbstractCompiledTemplate::class);
        $result = $instance->isCompiled();
        $this->assertTrue($result);
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
        $this->assertEquals($before, $after);
    }
}
