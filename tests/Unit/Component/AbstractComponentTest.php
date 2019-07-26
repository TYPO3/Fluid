<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\AlternativeComponentFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\ComponentFixture;
use TYPO3Fluid\Fluid\Tests\Unit\Component\Fixtures\TransparentComponentFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Test for base methods on AbstractComponent
 */
class AbstractComponentTest extends UnitTestCase
{
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
        $unnamedChild = new ComponentFixture();
        $transparentParent = new TransparentComponentFixture();
        $transparentParent->addChild($namedChild);
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
}