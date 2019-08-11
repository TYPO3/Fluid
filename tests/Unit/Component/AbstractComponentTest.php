<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\EmbeddedComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\AlternativeComponentFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\ComponentFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\TransparentComponentFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\ParameterViewHelper;

/**
 * Test for base methods on AbstractComponent
 */
class AbstractComponentTest extends UnitTestCase
{
    /**
     * @test
     */
    public function setArgumentsSetsArguments(): void
    {
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        $arguments = new ArgumentCollection();
        $subject->setArguments($arguments);
        $this->assertSame($arguments, $subject->getArguments());
    }

    /**
     * @test
     */
    public function castingNonStringCompatibleChildComponentsThrowsError(): void
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider(['foo' => new UserWithoutToString('user')]));
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        // Note: two identical child components is intentional; more than one must exist for casting to occur.
        $subject->addChild(new ObjectAccessorNode('foo'));
        $subject->addChild(new ObjectAccessorNode('foo'));
        $this->setExpectedException(Exception::class);
        $subject->evaluate($context);
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function addChildCalledWithParameterViewHelperRegistersArgument(): void
    {
        $context = new RenderingContextFixture();
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        $definitionArguments = ['name' => 'foo', 'type' => 'string', 'description' => 'Test'];

        $child = (new ParameterViewHelper())->onOpen($context);
        $child->getArguments()->setRenderingContext($context)->assignAll($definitionArguments);

        $subject->getArguments()->setRenderingContext($context);
        $subject->addChild($child);

        $expectedDefinitions = ['foo' => new ArgumentDefinition('foo', 'string', 'Test', false)];
        $this->assertEquals($expectedDefinitions, $subject->getArguments()->getDefinitions());
    }

    /**
     * @test
     * @dataProvider getNamedChildErrorTestValues
     * @param iterable $children
     * @param string $name
     * @throws \ReflectionException
     */
    public function getNamedThrowsErrorWhenNotFound(iterable $children, string $name): void
    {
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        foreach ($children as $child) {
            $subject->addChild($child);
        }
        $this->expectException(ChildNotFoundException::class);
        $subject->getNamedChild($name);
    }

    public function getNamedChildErrorTestValues(): array
    {
        $namedChild = new ComponentFixture('named');
        $unnamedChild = new ComponentFixture();
        $transparentParent = new TransparentComponentFixture();
        $transparentParent->addChild($namedChild);
        return [
            'not-matching named child as not found' => [
                [$unnamedChild],
                'named',
            ],
            'not-matching named child in transparent parent' => [
                [$unnamedChild, $transparentParent],
                'notMatchedName',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getNamedChildTestValues
     * @param iterable $children
     * @param string $name
     * @param ComponentInterface $expected
     * @throws \ReflectionException
     */
    public function getNamedChildReturnsExpectedChild(iterable $children, string $name, ComponentInterface $expected): void
    {
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        foreach ($children as $child) {
            $subject->addChild($child);
        }
        $this->assertSame($expected, $subject->getNamedChild($name));
    }

    public function getNamedChildTestValues(): array
    {
        $namedChild = new ComponentFixture('named');
        $namedRoot = new ComponentFixture('root');
        $unnamedChild = new ComponentFixture();
        $transparentParent = new TransparentComponentFixture();
        $emptyTransparentParent = new TransparentComponentFixture();
        $transparentParent->addChild($namedChild);
        $namedRoot->addChild($namedChild);
        return [
            'named child as immediate root' => [
                [$unnamedChild, $namedChild],
                'named',
                $namedChild,
            ],
            'named child in transparent parent' => [
                [$transparentParent],
                'named',
                $namedChild,
            ],
            'named child in named child' => [
                [$namedRoot],
                'root.named',
                $namedChild,
            ],
            'swallows ChildNotFound in transparent parent' => [
                [$emptyTransparentParent, $namedChild],
                'named',
                $namedChild,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getTypedChildrenTestValues
     * @param iterable $children
     * @param string $typeClassName
     * @param string|null $name
     * @param ComponentInterface $expected
     * @throws \ReflectionException
     */
    public function getTypedChildrenReturnsExpectedChildren(iterable $children, string $typeClassName, ?string $name, ComponentInterface $expected): void
    {
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        foreach ($children as $child) {
            $subject->addChild($child);
        }
        $this->assertEquals($expected, $subject->getTypedChildren($typeClassName, $name));
    }

    public function getTypedChildrenTestValues(): array
    {
        $unnamedTransparentWithoutChildren = new ComponentFixture();
        $unnamedTransparentWithChildren = new ComponentFixture();
        $namedNestedNonTransparentWithoutChildren = new AlternativeComponentFixture('nested');
        $namedNonTransparentWithoutChildren = new ComponentFixture('name1');
        $namedTransparentWithChildren = new TransparentComponentFixture('name2');
        $namedNonTransparentWithoutChildrenAlternative = new AlternativeComponentFixture('name3');
        $namedTransparentWithChildren->addChild($namedNestedNonTransparentWithoutChildren)->addChild(new ComponentFixture());
        $unnamedTransparentWithChildren->addChild($namedNestedNonTransparentWithoutChildren)->addChild(new ComponentFixture());

        $children = [
            $unnamedTransparentWithoutChildren,
            $unnamedTransparentWithChildren,
            $namedNonTransparentWithoutChildrenAlternative,
            $namedNonTransparentWithoutChildren,
            $namedTransparentWithChildren,
        ];

        return [
            'no children gets universal type and null name then returns empty collection' => [
                [],
                ComponentInterface::class,
                null,
                new RootNode(),
            ],
            'single child gets universal type and not-null name' => [
                [$namedNonTransparentWithoutChildren],
                ComponentInterface::class,
                'name1',
                (new RootNode())->addChild($namedNonTransparentWithoutChildren),
            ],
            'universal type and null name' => [
                $children,
                ComponentInterface::class,
                null,
                (new RootNode())
                    ->addChild($unnamedTransparentWithoutChildren)
                    ->addChild($unnamedTransparentWithChildren)
                    ->addChild($namedNonTransparentWithoutChildrenAlternative)
                    ->addChild($namedNonTransparentWithoutChildren)
                    ->addChild($namedTransparentWithChildren)
                    ->addChild($namedNestedNonTransparentWithoutChildren)
                    ->addChild(new ComponentFixture())
                ,
            ],
            'universal type and not-null name' => [
                $children,
                ComponentInterface::class,
                'name1',
                (new RootNode())->addChild($namedNonTransparentWithoutChildren),
            ],
            'specific type and null name' => [
                $children,
                AlternativeComponentFixture::class,
                null,
                (new RootNode())
                    ->addChild($namedNonTransparentWithoutChildrenAlternative)
                    ->addChild($namedNestedNonTransparentWithoutChildren),
            ],
            'specific type and not-null name' => [
                $children,
                AlternativeComponentFixture::class,
                'name3',
                (new RootNode())->addChild($namedNonTransparentWithoutChildrenAlternative),
            ],
            'specific type and not-null name that does not match' => [
                $children,
                AlternativeComponentFixture::class,
                'notMatchingName',
                new RootNode(),
            ],
            'universal type and not-null dotted name' => [
                $children,
                ComponentInterface::class,
                'name2.nested',
                (new RootNode())->addChild($namedNestedNonTransparentWithoutChildren),
            ],
            'universal type and not-null dotted name that does not match' => [
                $children,
                ComponentInterface::class,
                'not.matching.name',
                new RootNode(),
            ],
            'with transparent using specific type and not-null name gets nested child' => [
                $children,
                AlternativeComponentFixture::class,
                'nested',
                (new RootNode())->addChild($namedNestedNonTransparentWithoutChildren),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFlattenTestValues
     * @param ComponentInterface $subject
     * @param bool $extract
     * @param mixed $expected
     */
    public function flattenReturnsExpectedValue(ComponentInterface $subject, bool $extract, $expected): void
    {
        $this->assertSame($expected, $subject->flatten($extract));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getFlattenTestValues(): array
    {
        $textNode = new TextNode('foo');
        $selfReturn = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        return [
            'returns null for empty children with extract true' => [
                $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass(),
                true,
                null,
            ],
            'returns self for empty children with extract false' => [
                $selfReturn,
                false,
                $selfReturn,
            ],
            'returns single child text value if single child TextNode and extract true' => [
                $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass()->setChildren([new TextNode('foo')]),
                true,
                'foo',
            ],
            'returns single child if single child TextNode and extract false' => [
                $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass()->setChildren([$textNode]),
                false,
                $textNode
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getEvaluateChildrenTestValues
     * @param iterable $children
     * @param mixed $expected
     * @throws \ReflectionException
     */
    public function evaluateChildrenReturnsExpectedValue(iterable $children, $expected): void
    {
        /** @var ComponentInterface $subject */
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        $subject->setChildren($children);
        $this->assertSame($expected, $subject->evaluate(new RenderingContextFixture()));
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getEvaluateChildrenTestValues(): array
    {
        $arrayReturningComponent = $this->getMockBuilder(ComponentInterface::class)->getMockForAbstractClass();
        $arrayReturningComponent->expects($this->once())->method('evaluate')->willReturn(['foo' => 'bar']);
        $embeddedComponent = $this->getMockBuilder(EmbeddedComponentInterface::class)->getMockForAbstractClass();
        return [
            'returns null for empty children' => [
                [],
                null,
            ],
            'multiple text nodes become concatenated string' => [
                [new TextNode('foo'), new TextNode('bar'), new TextNode('baz')],
                'foobarbaz',
            ],
            'skips embedded components' => [
                [new TextNode('foo'), $embeddedComponent, new TextNode('baz')],
                'foobaz',
            ],
            'executes single child without cast to string' => [
                [$arrayReturningComponent],
                ['foo' => 'bar'],
            ],
        ];
    }

    /**
     * @test
     * @throws \ReflectionException
     */
    public function allowsArbitraryArgumentsByDefault(): void
    {
        /** @var ComponentInterface $subject */
        $subject = $this->getMockBuilder(AbstractComponent::class)->getMockForAbstractClass();
        $this->assertSame(true, $subject->allowUndeclaredArgument('random-argument'));
    }
}