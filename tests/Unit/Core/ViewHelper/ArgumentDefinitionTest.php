<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Definition\Annotation\Annotation;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;

final class ArgumentDefinitionTest extends TestCase
{
    #[Test]
    public function objectStoresDataCorrectly(): void
    {
        $name = 'This is a name';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;
        $annotations = [new Annotation(['see' => 'https://example.com']), new Annotation(['deprecated' => 'since 2.0.0, will be removed in 2.0.0'])];
        $argumentDefinition = new ArgumentDefinition($name, $type, $description, $isRequired, null, null, $annotations);

        self::assertEquals($argumentDefinition->getName(), $name, 'Name could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getDescription(), $description, 'Description could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getType(), $type, 'Type could not be retrieved correctly');
        self::assertEquals($argumentDefinition->isRequired(), $isRequired, 'Required flag could not be retrieved correctly.');
        self::assertSame($argumentDefinition->getAnnotations(), $annotations);
    }

    public static function determinesBooleanCorrectlyDataProvider(): array
    {
        return [
            [new ArgumentDefinition('test', 'bool', '', false), true],
            [new ArgumentDefinition('test', 'boolean', '', false), true],
            [new ArgumentDefinition('test', 'bool[]', '', false), false],
            [new ArgumentDefinition('test', 'string', '', false), false],
        ];
    }

    #[Test]
    #[DataProvider('determinesBooleanCorrectlyDataProvider')]
    public function determinesBooleanCorrectly(ArgumentDefinition $argumentDefinition, bool $expected): void
    {
        self::assertSame($expected, $argumentDefinition->isBooleanType());
    }

    #[Test]
    public function constructorThrowsExceptionWhenRequiredWithDefaultValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1754235900);
        $this->expectExceptionMessage('ArgumentDefinition "test" cannot have a default value while also being required. Either remove the default or mark it as optional.');

        new ArgumentDefinition('test', 'string', 'Test argument', true, 'defaultValue');
    }

    public static function compilationCreatesEqualObjectDataProvider(): array
    {
        return [
            [new ArgumentDefinition('test', 'bool', 'description', false, 'default', null)],
            [new ArgumentDefinition('test', 'bool', 'description', true, null, true, [new Annotation(['see' => 'https://example.com', 'something' => 'else'])])],
            [new ArgumentDefinition('test', 'bool', 'description', false, 'default', false, [new Annotation(['see' => 'https://example.com']), new Annotation(['deprecated' => 'since 2.0.0, will be removed in 2.0.0'])])],
        ];
    }

    #[Test]
    #[DataProvider('compilationCreatesEqualObjectDataProvider')]
    public function compilationCreatesEqualObject(ArgumentDefinition $argumentDefinition): void
    {
        self::assertEquals($argumentDefinition, eval('return ' . $argumentDefinition->compile() . ';'));
    }
}
