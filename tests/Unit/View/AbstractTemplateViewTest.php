<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Compiler\AbstractCompiledTemplate;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;

class AbstractTemplateViewTest extends UnitTestCase
{
    /**
     * @var AbstractTemplateView
     */
    protected $view;

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    /**
     * @var ViewHelperVariableContainer
     */
    protected $viewHelperVariableContainer;

    /**
     * @var VariableProviderInterface&MockObject
     */
    protected $templateVariableContainer;

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
     */
    public function testGetRenderingContextReturnsExpectedRenderingContext()
    {
        $result = $this->view->getRenderingContext();
        self::assertSame($this->renderingContext, $result);
    }

    /**
     * @test
     */
    public function testGetViewHelperResolverReturnsExpectedViewHelperResolver()
    {
        $viewHelperResolver = $this->getMock(ViewHelperResolver::class);
        $this->renderingContext->setViewHelperResolver($viewHelperResolver);
        $result = $this->view->getViewHelperResolver();
        self::assertSame($viewHelperResolver, $result);
    }

    /**
     * @test
     */
    public function assignAddsValueToTemplateVariableContainer()
    {
        $this->templateVariableContainer->expects(self::exactly(2))->method('add')->withConsecutive(
            ['foo', 'FooValue'],
            ['bar', 'BarValue']
        );
        $this->view
            ->assign('foo', 'FooValue')
            ->assign('bar', 'BarValue');
    }

    /**
     * @test
     */
    public function assignCanOverridePreviouslyAssignedValues()
    {
        $this->templateVariableContainer->expects(self::exactly(2))->method('add')->withConsecutive(
            ['foo', 'FooValue'],
            ['foo', 'FooValueOverridden']
        );
        $this->view->assign('foo', 'FooValue');
        $this->view->assign('foo', 'FooValueOverridden');
    }

    /**
     * @test
     */
    public function assignMultipleAddsValuesToTemplateVariableContainer()
    {
        $this->templateVariableContainer->expects(self::exactly(3))->method('add')->withConsecutive(
            ['foo', 'FooValue'],
            ['bar', 'BarValue'],
            ['baz', 'BazValue']
        );
        $this->view
            ->assignMultiple(['foo' => 'FooValue', 'bar' => 'BarValue'])
            ->assignMultiple(['baz' => 'BazValue']);
    }

    /**
     * @test
     */
    public function assignMultipleCanOverridePreviouslyAssignedValues()
    {
        $this->templateVariableContainer->expects(self::exactly(3))->method('add')->withConsecutive(
            ['foo', 'FooValue'],
            ['foo', 'FooValueOverridden'],
            ['bar', 'BarValue']
        );
        $this->view->assign('foo', 'FooValue');
        $this->view->assignMultiple(['foo' => 'FooValueOverridden', 'bar' => 'BarValue']);
    }

    /**
     * @test
     * @dataProvider getRenderSectionExceptionTestValues
     * @param bool $compiled
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
        $parsedTemplate->expects(self::once())->method('isCompiled')->willReturn($compiled);
        $parsedTemplate->expects(self::any())->method('getVariableContainer')->willReturn(new StandardVariableProvider(
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
        $view->expects(self::once())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
        $view->expects(self::once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $view->expects(self::once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
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
     * @param bool $exists
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
        $parsedTemplate->expects(self::once())->method('isCompiled')->willReturn(true);
        $view = $this->getMockForAbstractClass(
            AbstractTemplateView::class,
            [],
            '',
            false,
            false,
            true,
            ['getCurrentParsedTemplate', 'getCurrentRenderingType', 'getCurrentRenderingContext']
        );
        $view->expects(self::atLeastOnce())->method('getCurrentRenderingContext')->willReturn($this->renderingContext);
        $view->expects(self::once())->method('getCurrentRenderingType')->willReturn(AbstractTemplateView::RENDERING_LAYOUT);
        $view->expects(self::once())->method('getCurrentParsedTemplate')->willReturn($parsedTemplate);
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
