<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeDefaultRenderStaticViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Fixtures\RenderMethodFreeViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;

/**
 * Testcase for AbstractViewHelper
 */
class AbstractViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        return [];
    }

    /**
     * @var array
     */
    protected $fixtureMethodParameters = [
        'param1' => [
            'position' => 0,
            'optional' => false,
            'type' => 'integer',
            'defaultValue' => null
        ],
        'param2' => [
            'position' => 1,
            'optional' => false,
            'type' => 'array',
            'array' => true,
            'defaultValue' => null
        ],
        'param3' => [
            'position' => 2,
            'optional' => true,
            'type' => 'string',
            'array' => false,
            'defaultValue' => 'default'
        ],
    ];

    /**
     * @var array
     */
    protected $fixtureMethodTags = [
        'param' => [
            'integer $param1 P1 Stuff',
            'array $param2 P2 Stuff',
            'string $param3 P3 Stuff'
        ]
    ];

    /**
     * @test
     */
    public function testOnOpenReturnsInstance()
    {
        $argumentDefinitions = [
            'foo' => new ArgumentDefinition('foo', 'string', 'Foo', true),
        ];
        $viewHelper = $this->getMockBuilder(AbstractViewHelper::class)->setMethods(['dummy'])->getMockForAbstractClass();
        $expectedArguments = [
            'foo' => 'foovalue',
            'undeclared' => 'some',
        ];
        $result = $viewHelper->onOpen(new RenderingContextFixture(), (new ArgumentCollection($argumentDefinitions))->assignAll($expectedArguments));
        $this->assertSame($viewHelper, $result);
    }

    /**
     * @test
     */
    public function testExecuteCallsExpectedFunctions()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractViewHelper::class,
            ['callRenderMethod']
        );
        $expectedArguments = [
            'foo' => 'foovalue',
            'undeclared' => 'some',
        ];
        $viewHelper->expects($this->once())->method('callRenderMethod');
        $viewHelper->execute(new RenderingContextFixture(), (new ArgumentCollection())->setRenderingContext(new RenderingContextFixture())->assignAll($expectedArguments));
    }

    /**
     * @test
     */
    public function argumentsCanBeRegistered(): void
    {
        $viewHelper = $this->getAccessibleMockForAbstractClass(AbstractViewHelper::class);

        $name = 'name_something';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;
        $expected = new ArgumentDefinition($name, $type, $description, $isRequired);

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $this->assertEquals([$name => $expected], $viewHelper->getArguments()->getDefinitions(), 'Argument definitions not returned correctly.');
    }

    /**
     * @test
     */
    public function testRenderChildrenCallsRenderChildrenClosureIfSet()
    {
        $viewHelper = $this->getMockForAbstractClass(AbstractViewHelper::class);
        $viewHelper->setRenderChildrenClosure(function (): string {
            return 'foobar';
        });
        $method = new \ReflectionMethod($viewHelper, 'renderChildren');
        $method->setAccessible(true);
        $result = $method->invoke($viewHelper);
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     */
    public function testCallRenderMethodCanRenderViewHelperWithoutRenderMethodAndCallsRenderStatic(): void
    {
        $subject = new RenderMethodFreeViewHelper();
        $context = new RenderingContextFixture();
        $this->assertSame('I was rendered', $subject->onOpen($context)->execute($context, $subject->getArguments()->setRenderingContext($context)));
    }

    /**
     * @test
     */
    public function testCallRenderMethodOnViewHelperWithoutRenderMethodWithoutRenderStaticWithoutExecuteReturnsChildContent(): void
    {
        $subject = new RenderMethodFreeDefaultRenderStaticViewHelper();
        $subject->addChild(new TextNode('foo'));
        $context = new RenderingContextFixture();
        $this->assertSame('foo', $subject->onOpen($context)->execute($context, $subject->getArguments()));
    }
}
