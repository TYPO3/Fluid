<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for ParsingState
 *
 */
class RenderingContextTest extends UnitTestCase
{

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    public function setUp()
    {
        $this->renderingContext = new RenderingContextFixture();
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyNameTestValues
     */
    public function testGetter($property, $value)
    {
        $view = new TemplateView();
        $subject = $this->getAccessibleMock(RenderingContext::class, ['dummy'], [$view]);
        $subject->_set($property, $value);
        $getter = 'get' . ucfirst($property);
        $this->assertSame($value, $subject->$getter());
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyNameTestValues
     */
    public function testSetter($property, $value)
    {
        $view = new TemplateView();
        $subject = new RenderingContext($view);
        $setter = 'set' . ucfirst($property);
        $subject->$setter($value);
        $this->assertAttributeSame($value, $property, $subject);
    }

    /**
     * @return array
     */
    public function getPropertyNameTestValues()
    {
        return [
            ['variableProvider', new StandardVariableProvider(['foo' => 'bar'])],
            ['viewHelperResolver', $this->getMock(ViewHelperResolver::class)],
            ['viewHelperInvoker', $this->getMock(ViewHelperInvoker::class)],
            ['controllerName', 'foobar-controllerName'],
            ['controllerAction', 'foobar-controllerAction'],
            ['expressionNodeTypes', ['Foo', 'Bar']],
            ['templatePaths', $this->getMock(TemplatePaths::class)],
            ['cache', $this->getMock(SimpleFileCache::class)],
            ['templateParser', $this->getMock(TemplateParser::class)],
            ['templateCompiler', $this->getMock(TemplateCompiler::class)],
            ['templateProcessors', [$this->getMock(TemplateProcessorInterface::class), $this->getMock(TemplateProcessorInterface::class)]]
        ];
    }

    /**
     * @test
     */
    public function templateVariableContainerCanBeReadCorrectly()
    {
        $templateVariableContainer = $this->getMock(StandardVariableProvider::class);
        $this->renderingContext->setVariableProvider($templateVariableContainer);
        $this->assertSame($this->renderingContext->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
    }

    /**
     * @test
     */
    public function viewHelperVariableContainerCanBeReadCorrectly()
    {
        $viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class);
        $this->renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
        $this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
    }

    /**
     * @test
     */
    public function testIsCacheEnabled()
    {
        $subject = new RenderingContext($this->getMock(TemplateView::class));
        $this->assertFalse($subject->isCacheEnabled());
        $subject->setCache($this->getMock(SimpleFileCache::class));
        $this->assertTrue($subject->isCacheEnabled());
    }
}
