<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Component\Argument;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Component\Argument\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Tests for the the ArgumentCollection class
 */
class ArgumentCollectionTest extends UnitTestCase
{

    /**
     * @test
     * @dataProvider getBooleanSetterTestValues
     * @param mixed $value
     * @param mixed $expected
     */
    public function settingBooleanPropertiesConvertsInputValue($value, $expected): void
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider(['fooTrue' => 1, 'fooFalse' => 0]));
        $subject = new ArgumentCollection();
        $subject->setRenderingContext($context);
        $subject->addDefinition(new ArgumentDefinition('bool', 'boolean', 'Boolean argument', true));
        $subject['bool'] = $value;
        $this->assertSame($expected, $subject['bool']);
    }

    public function getBooleanSetterTestValues(): array
    {
        return [
            'true integer as int' => [1, true],
            'false integer as int' => [0, false],
            'true integer as string' => ['1', true],
            'false integer as string' => ['0', false],
            'true as string' => ['true', true],
            'false as string' => ['false', false],
            'true as uppercase string' => ['TRUE', true],
            'false as uppercase string' => ['FALSE', false],
            'true as boolean' => ['TRUE', true],
            'false as boolean' => ['FALSE', false],
            'true-ish expression' => ['astring', true],
            'false-ish expression' => ['', false],
            'null expression' => [null, false],
        ];
    }

    /**
     * @test
     */
    public function getAllRawReturnsNotEvaluatedComponents(): void
    {
        $child = new TextNode('foo');
        $subject = new ArgumentCollection();
        $subject['test'] = $child;
        $this->assertSame(['test' => $child], $subject->getAllRaw());
    }

    /**
     * @test
     * @dataProvider getArgumentArrayAccessTestValues
     * @param array $arguments
     * @param array $definitions
     * @param array|null $expected
     */
    public function evaluatesArgumentsOnArrayAccess(array $arguments, array $definitions = [], array $variables = [], ?array $expected = null): void
    {
        $expected = $expected ?? $arguments;
        $subject = new ArgumentCollection();
        foreach ($definitions as $definition) {
            $subject->addDefinition($definition);
        }
        $subject->assignAll($arguments);
        if (!empty($variables)) {
            $context = new RenderingContextFixture();
            $context->setVariableProvider(new StandardVariableProvider($variables));
            $subject->setRenderingContext($context);
        }
        foreach ($expected as $name => $value) {
            $this->assertEquals($value, $subject[$name]);
        }
        $this->assertSame($expected, $subject->getArrayCopy());
    }

    public function getArgumentArrayAccessTestValues(): array
    {
        $fooStringRequired = new ArgumentDefinition('foo', 'string', 'Foo', true);
        $barStringRequired = new ArgumentDefinition('bar', 'string', 'Bar', true);
        $booleanDefaultTrue = new ArgumentDefinition('bool', 'boolean', 'Boolean', false, true);
        return [
            'supports raw values without definitions' => [
                ['foo' => 'foo', 'bar' => 'bar'],
            ],
            'supports raw values with definitions' => [
                ['foo' => 'foo', 'bar' => 'bar'],
                [$fooStringRequired, $barStringRequired]
            ],
            'supports component values with definitions' => [
                ['foo' => new ObjectAccessorNode('foo'), 'bar' => new ObjectAccessorNode('bar')],
                [$fooStringRequired, $barStringRequired],
                ['foo' => 'foo', 'bar' => 'bar'],
                ['foo' => 'foo', 'bar' => 'bar'],
            ],
            'boolean arguments with definitions sets default value' => [
                [],
                [$booleanDefaultTrue],
                [],
                ['bool' => true],
            ],
        ];
    }
}