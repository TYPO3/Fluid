<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Testcase for ParsingState
 */
class RenderingContextTest extends UnitTestCase
{

    /**
     * @var RenderingContextInterface
     */
    protected $renderingContext;

    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyNameTestValues
     */
    public function testGetter(string $property, $value): void
    {
        $subject = $this->getAccessibleMock(RenderingContext::class, ['dummy']);
        $subject->_set($property, $value);
        $getter = 'get' . ucfirst($property);
        $this->assertSame($value, $subject->$getter());
    }

    /**
     * @param string $property
     * @param mixed $value
     * @dataProvider getPropertyNameTestValues
     */
    public function testSetter(string $property, $value): void
    {
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst($property);
        $subject->$setter($value);
        $this->assertAttributeSame($value, $property, $subject);
    }

    /**
     * @return array
     */
    public function getPropertyNameTestValues(): array
    {
        return [
            ['variableProvider', new StandardVariableProvider(['foo' => 'bar'])],
            ['viewHelperResolver', $this->getMock(ViewHelperResolver::class)],
            ['expressionNodeTypes', ['Foo', 'Bar']],
            ['templatePaths', $this->getMock(TemplatePaths::class)],
            ['templateParser', $this->getMockBuilder(TemplateParser::class)->disableOriginalConstructor()->getMock()],
        ];
    }

    /**
     * @test
     */
    public function templateVariableContainerCanBeReadCorrectly(): void
    {
        $templateVariableContainer = $this->getMock(StandardVariableProvider::class);
        $this->renderingContext->setVariableProvider($templateVariableContainer);
        $this->assertSame($this->renderingContext->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
    }

    /**
     * @test
     */
    public function viewHelperVariableContainerCanBeReadCorrectly(): void
    {
        $viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class);
        $this->renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);
        $this->assertSame($viewHelperVariableContainer, $this->renderingContext->getViewHelperVariableContainer());
    }
}
