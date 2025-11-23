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
        $isMethodParameter = true;
        $argumentDefinition = new ArgumentDefinition($name, $type, $description, $isRequired, null);

        self::assertEquals($argumentDefinition->getName(), $name, 'Name could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getDescription(), $description, 'Description could not be retrieved correctly.');
        self::assertEquals($argumentDefinition->getType(), $type, 'Type could not be retrieved correctly');
        self::assertEquals($argumentDefinition->isRequired(), $isRequired, 'Required flag could not be retrieved correctly.');
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
    public function metadataDefaultsToEmptyArray(): void
    {
        $argumentDefinition = new ArgumentDefinition('test', 'string', '', false);
        self::assertSame([], $argumentDefinition->getMetadata());
    }

    #[Test]
    public function metadataIsStored(): void
    {
        $metadata = ['source' => 'unit-test', 'flag' => true];
        $argumentDefinition = new ArgumentDefinition('test', 'string', '', false, null, null, $metadata);
        self::assertSame($metadata, $argumentDefinition->getMetadata());
    }

    #[Test]
    public function constructorThrowsExceptionWhenRequiredWithDefaultValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1754235900);
        $this->expectExceptionMessage('ArgumentDefinition "test" cannot have a default value while also being required. Either remove the default or mark it as optional.');

        new ArgumentDefinition('test', 'string', 'Test argument', true, 'defaultValue');
    }
}
