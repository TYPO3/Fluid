<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\FluidRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Testcase for the TemplateView
 */
class AbstractTemplateViewTest extends UnitTestCase
{

    /**
     * @var AbstractTemplateView
     */
    protected $view;

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    /**
     * @var ViewHelperVariableContainer
     */
    protected $viewHelperVariableContainer;

    /**
     * @var VariableProviderInterface
     */
    protected $templateVariableContainer;

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->templateVariableContainer = $this->getMock(StandardVariableProvider::class);
        $this->viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class, ['setView']);
        $this->renderingContext = new RenderingContextFixture();
        $this->renderingContext->viewHelperVariableContainer = $this->viewHelperVariableContainer;
        $this->renderingContext->variableProvider = $this->templateVariableContainer;
        $this->view = $this->getMockForAbstractClass(AbstractTemplateView::class);
        $this->view->setRenderingContext($this->renderingContext);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderSectionUsesCustomClosures(): void
    {
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getTemplateSource', 'getTemplateIdentifier'])->getMock();
        $templatePaths->expects($this->atLeastOnce())->method('getTemplateSource')->willReturn('<f:section name="Foo">foo</f:section>');
        $this->renderingContext->setTemplatePaths($templatePaths);
        $output = $this->view->renderSection('Foo');
        $this->assertSame('foo', $output);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function renderPartialDelegatesToFluidRenderer(): void
    {
        $renderer = $this->getMockBuilder(FluidRenderer::class)->setMethods(['renderPartial'])->disableOriginalConstructor()->getMock();
        $renderer->expects($this->once())->method('renderPartial');
        $this->renderingContext->setRenderer($renderer);
        $this->view->renderPartial('foo', null, []);
    }

    /**
     * @test
     */
    public function testGetRenderingContextReturnsExpectedRenderingContext(): void
    {
        $result = $this->view->getRenderingContext();
        $this->assertSame($this->renderingContext, $result);
    }

    /**
     * @test
     */
    public function testGetViewHelperResolverReturnsExpectedViewHelperResolver(): void
    {
        $viewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)->disableOriginalConstructor()->getMock();
        $this->renderingContext->setViewHelperResolver($viewHelperResolver);
        $result = $this->view->getViewHelperResolver();
        $this->assertSame($viewHelperResolver, $result);
    }

    /**
     * @test
     */
    public function assignAddsValueToTemplateVariableContainer(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('bar', 'BarValue');

        $this->view
            ->assign('foo', 'FooValue')
            ->assign('bar', 'BarValue');
    }

    /**
     * @test
     */
    public function assignCanOverridePreviouslyAssignedValues(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');

        $this->view->assign('foo', 'FooValue');
        $this->view->assign('foo', 'FooValueOverridden');
    }

    /**
     * @test
     */
    public function assignMultipleAddsValuesToTemplateVariableContainer(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('bar', 'BarValue');
        $this->templateVariableContainer->expects($this->at(2))->method('add')->with('baz', 'BazValue');

        $this->view
            ->assignMultiple(['foo' => 'FooValue', 'bar' => 'BarValue'])
            ->assignMultiple(['baz' => 'BazValue']);
    }

    /**
     * @test
     */
    public function assignMultipleCanOverridePreviouslyAssignedValues(): void
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');
        $this->templateVariableContainer->expects($this->at(2))->method('add')->with('bar', 'BarValue');

        $this->view->assign('foo', 'FooValue');
        $this->view->assignMultiple(['foo' => 'FooValueOverridden', 'bar' => 'BarValue']);
    }

}
