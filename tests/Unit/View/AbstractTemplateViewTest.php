<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;

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
     * @var TemplateVariableContainer
     */
    protected $templateVariableContainer;

    /**
     * Sets up this test case
     *
     * @return void
     */
    public function setUp()
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
     */
    public function testGetRenderingContextReturnsExpectedRenderingContext()
    {
        $result = $this->view->getRenderingContext();
        $this->assertSame($this->renderingContext, $result);
    }

    /**
     * @test
     */
    public function testGetViewHelperResolverReturnsExpectedViewHelperResolver()
    {
        $viewHelperResolver = $this->getMock(ViewHelperResolver::class);
        $this->renderingContext->setViewHelperResolver($viewHelperResolver);
        $result = $this->view->getViewHelperResolver();
        $this->assertSame($viewHelperResolver, $result);
    }

    /**
     * @test
     */
    public function assignAddsValueToTemplateVariableContainer()
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
    public function assignCanOverridePreviouslyAssignedValues()
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');

        $this->view->assign('foo', 'FooValue');
        $this->view->assign('foo', 'FooValueOverridden');
    }

    /**
     * @test
     */
    public function assignMultipleAddsValuesToTemplateVariableContainer()
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
    public function assignMultipleCanOverridePreviouslyAssignedValues()
    {
        $this->templateVariableContainer->expects($this->at(0))->method('add')->with('foo', 'FooValue');
        $this->templateVariableContainer->expects($this->at(1))->method('add')->with('foo', 'FooValueOverridden');
        $this->templateVariableContainer->expects($this->at(2))->method('add')->with('bar', 'BarValue');

        $this->view->assign('foo', 'FooValue');
        $this->view->assignMultiple(['foo' => 'FooValueOverridden', 'bar' => 'BarValue']);
    }

    /**
     * @test
     * @dataProvider getRenderSectionExceptionTestValues
     * @param boolean $compiled
     * @test
     */
    public function testRenderSectionThrowsExceptionIfSectionMissingAndNotIgnoringUnknown($compiled)
    {
        $parsedTemplate = $this->getMockForAbstractClass(
            AbstractCompiledTemplate::class,
            [],
            '',
            false,
            false,
            true,
            ['isCompiled', 'getVariableContainer']
        );
        $parsedTemplate->expects($this->once())->method('isCompiled')->willReturn($compiled);
        $parsedTemplate->expects($this->any())->method('getVariableContainer')->willReturn(new StandardVariableProvider(
            ['sections' => []]
        ));
        $view = $this->getMockForAbstractClass(
            AbstractTemplateView::class,
            [],
            '',
            false,
            false,
            true,
            ['getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext']
        );
        $view->expects($this->once())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
        $view->expects($this->once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $view->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $this->setExpectedException(InvalidSectionException::class);
        $view->renderSection('Missing');
    }

    /**
     * @return array
     */
    public function getRenderSectionExceptionTestValues()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @test
     * @dataProvider getRenderSectionCompiledTestValues
     * @param boolean $exists
     * @test
     */
    public function testRenderSectionOnCompiledTemplate($exists)
    {
        if ($exists) {
            $sectionMethodName = 'section_' . sha1('Section');
        } else {
            $sectionMethodName = 'test';
        }
        $parsedTemplate = $this->getMockForAbstractClass(
            AbstractCompiledTemplate::class,
            [],
            '',
            false,
            false,
            true,
            ['isCompiled', 'getVariableContainer', $sectionMethodName]
        );
        $parsedTemplate->expects($this->once())->method('isCompiled')->willReturn(true);
        $view = $this->getMockForAbstractClass(
            AbstractTemplateView::class,
            [],
            '',
            false,
            false,
            true,
            ['getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext']
        );
        $view->expects($this->atLeastOnce())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
        $view->expects($this->once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $view->expects($this->once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
        $view->renderSection('Section', [], true);
    }

    /**
     * @return array
     */
    public function getRenderSectionCompiledTestValues()
    {
        return [
            [true],
            [false]
        ];
    }
}
