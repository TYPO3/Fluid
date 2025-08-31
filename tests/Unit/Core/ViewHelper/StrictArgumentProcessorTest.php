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
use stdClass;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\StrictArgumentProcessor;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\ArrayAccessExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\BackedEnumExample;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;

final class StrictArgumentProcessorTest extends TestCase
{
    public static function isValidAndProcessDataProvider(): iterable
    {
        $dateTime = new \DateTime('now');
        $stdClass = new stdClass();
        $arrayIterator = new \ArrayIterator(['bar']);
        $arrayAccess = new ArrayAccessExample(['foo' => 'bar']);
        $stringable = new UserWithToString('foo');
        $countable = new class () implements \Countable {
            public function count(): int
            {
                return 2;
            }
        };
        $intval = intval(...);

        //
        // Boolean types
        //
        foreach (['boolean', 'bool'] as $type) {
            yield [
                'type' => $type,
                'value' => true,
                'expectedValidity' => true,
                'expectedProcessedValue' => true,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => false,
                'expectedValidity' => true,
                'expectedProcessedValue' => false,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => ['bad'],
                'expectedValidity' => false,
                'expectedProcessedValue' => ['bad'],
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => 1,
                'expectedValidity' => false,
                'expectedProcessedValue' => true,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 'foo',
                'expectedValidity' => false,
                'expectedProcessedValue' => true,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 0,
                'expectedValidity' => false,
                'expectedProcessedValue' => false,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => '',
                'expectedValidity' => false,
                'expectedProcessedValue' => false,
                'expectedProcessedValidity' => true,
            ];
            // De-facto, in most circumstances non-boolean values are converted by the parser
            yield [
                'type' => $type,
                'value' => (new BooleanNode(123))->evaluate(new RenderingContext()),
                'expectedValidity' => true,
                'expectedProcessedValue' => true,
                'expectedProcessedValidity' => true,
            ];
        }

        //
        // String
        //
        yield [
            'type' => 'string',
            'value' => true,
            'expectedValidity' => false,
            'expectedProcessedValue' => '1',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => false,
            'expectedValidity' => false,
            'expectedProcessedValue' => '',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => 2,
            'expectedValidity' => false,
            'expectedProcessedValue' => '2',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => 1,
            'expectedValidity' => false,
            'expectedProcessedValue' => '1',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => 0,
            'expectedValidity' => false,
            'expectedProcessedValue' => '0',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => -1,
            'expectedValidity' => false,
            'expectedProcessedValue' => '-1',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => 1.5,
            'expectedValidity' => false,
            'expectedProcessedValue' => '1.5',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => '',
            'expectedValidity' => true,
            'expectedProcessedValue' => '',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => 'test',
            'expectedValidity' => true,
            'expectedProcessedValue' => 'test',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string',
            'value' => null,
            'expectedValidity' => false,
            'expectedProcessedValue' => null,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => $stdClass,
            'expectedValidity' => false,
            'expectedProcessedValue' => $stdClass,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => $dateTime,
            'expectedValidity' => false,
            'expectedProcessedValue' => $dateTime,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => [],
            'expectedValidity' => false,
            'expectedProcessedValue' => [],
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => ['test'],
            'expectedValidity' => false,
            'expectedProcessedValue' => ['test'],
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => $arrayIterator,
            'expectedValidity' => false,
            'expectedProcessedValue' => $arrayIterator,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string',
            'value' => $stringable,
            'expectedValidity' => true,
            'expectedProcessedValue' => $stringable,
            'expectedProcessedValidity' => true,
        ];

        //
        // Integer types
        //
        foreach (['int', 'integer'] as $type) {
            yield [
                'type' => $type,
                'value' => true,
                'expectedValidity' => false,
                'expectedProcessedValue' => 1,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => false,
                'expectedValidity' => false,
                'expectedProcessedValue' => 0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 2,
                'expectedValidity' => true,
                'expectedProcessedValue' => 2,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 1,
                'expectedValidity' => true,
                'expectedProcessedValue' => 1,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 0,
                'expectedValidity' => true,
                'expectedProcessedValue' => 0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => -1,
                'expectedValidity' => true,
                'expectedProcessedValue' => -1,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 1.5,
                'expectedValidity' => false,
                'expectedProcessedValue' => 1,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => '',
                'expectedValidity' => false,
                'expectedProcessedValue' => 0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 'test',
                'expectedValidity' => false,
                'expectedProcessedValue' => 0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => null,
                'expectedValidity' => false,
                'expectedProcessedValue' => null,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $stdClass,
                'expectedValidity' => false,
                'expectedProcessedValue' => $stdClass,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $dateTime,
                'expectedValidity' => false,
                'expectedProcessedValue' => $dateTime,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => [],
                'expectedValidity' => false,
                'expectedProcessedValue' => [],
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => ['test'],
                'expectedValidity' => false,
                'expectedProcessedValue' => ['test'],
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $arrayIterator,
                'expectedValidity' => false,
                'expectedProcessedValue' => $arrayIterator,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $stringable,
                'expectedValidity' => false,
                'expectedProcessedValue' => $stringable,
                'expectedProcessedValidity' => false,
            ];
        }

        //
        // Float types
        //
        foreach (['float', 'double'] as $type) {
            yield [
                'type' => $type,
                'value' => true,
                'expectedValidity' => false,
                'expectedProcessedValue' => 1.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => false,
                'expectedValidity' => false,
                'expectedProcessedValue' => 0.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 2,
                'expectedValidity' => false,
                'expectedProcessedValue' => 2.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 1,
                'expectedValidity' => false,
                'expectedProcessedValue' => 1.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 0,
                'expectedValidity' => false,
                'expectedProcessedValue' => 0.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => -1,
                'expectedValidity' => false,
                'expectedProcessedValue' => -1.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 1.5,
                'expectedValidity' => true,
                'expectedProcessedValue' => 1.5,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => '',
                'expectedValidity' => false,
                'expectedProcessedValue' => 0.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => 'test',
                'expectedValidity' => false,
                'expectedProcessedValue' => 0.0,
                'expectedProcessedValidity' => true,
            ];
            yield [
                'type' => $type,
                'value' => null,
                'expectedValidity' => false,
                'expectedProcessedValue' => null,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $stdClass,
                'expectedValidity' => false,
                'expectedProcessedValue' => $stdClass,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $dateTime,
                'expectedValidity' => false,
                'expectedProcessedValue' => $dateTime,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => [],
                'expectedValidity' => false,
                'expectedProcessedValue' => [],
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => ['test'],
                'expectedValidity' => false,
                'expectedProcessedValue' => ['test'],
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $arrayIterator,
                'expectedValidity' => false,
                'expectedProcessedValue' => $arrayIterator,
                'expectedProcessedValidity' => false,
            ];
            yield [
                'type' => $type,
                'value' => $stringable,
                'expectedValidity' => false,
                'expectedProcessedValue' => $stringable,
                'expectedProcessedValidity' => false,
            ];
        }

        //
        // Objects
        //
        yield [
            'type' => 'object',
            'value' => $dateTime,
            'expectedValidity' => true,
            'expectedProcessedValue' => $dateTime,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'object',
            'value' => null,
            'expectedValidity' => false,
            'expectedProcessedValue' => null,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'DateTime',
            'value' => $dateTime,
            'expectedValidity' => true,
            'expectedProcessedValue' => $dateTime,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'DateTime',
            'value' => $arrayIterator,
            'expectedValidity' => false,
            'expectedProcessedValue' => $arrayIterator,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'DateTime',
            'value' => 'test',
            'expectedValidity' => false,
            'expectedProcessedValue' => 'test',
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'DateTime',
            'value' => null,
            'expectedValidity' => false,
            'expectedProcessedValue' => null,
            'expectedProcessedValidity' => false,
        ];
        // Interfaces
        yield [
            'type' => 'DateTimeInterface',
            'value' => $dateTime,
            'expectedValidity' => true,
            'expectedProcessedValue' => $dateTime,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'DateTimeInterface',
            'value' => $stdClass,
            'expectedValidity' => false,
            'expectedProcessedValue' => $stdClass,
            'expectedProcessedValidity' => false,
        ];
        // Enums
        yield [
            'type' => BackedEnumExample::class,
            'value' => BackedEnumExample::BAR,
            'expectedValidity' => true,
            'expectedProcessedValue' => BackedEnumExample::BAR,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => BackedEnumExample::class,
            'value' => $stdClass,
            'expectedValidity' => false,
            'expectedProcessedValue' => $stdClass,
            'expectedProcessedValidity' => false,
        ];

        //
        // Iterable
        //
        yield [
            'type' => 'iterable',
            'value' => [],
            'expectedValidity' => true,
            'expectedProcessedValue' => [],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'iterable',
            'value' => $arrayIterator,
            'expectedValidity' => true,
            'expectedProcessedValue' => $arrayIterator,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'iterable',
            'value' => $arrayAccess,
            'expectedValidity' => false,
            'expectedProcessedValue' => $arrayAccess,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'iterable',
            'value' => 'foo',
            'expectedValidity' => false,
            'expectedProcessedValue' => 'foo',
            'expectedProcessedValidity' => false,
        ];

        //
        // Countable
        //
        yield [
            'type' => 'countable',
            'value' => [],
            'expectedValidity' => true,
            'expectedProcessedValue' => [],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'countable',
            'value' => $countable,
            'expectedValidity' => true,
            'expectedProcessedValue' => $countable,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'countable',
            'value' => $arrayIterator,
            'expectedValidity' => true,
            'expectedProcessedValue' => $arrayIterator,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'countable',
            'value' => $arrayAccess,
            'expectedValidity' => false,
            'expectedProcessedValue' => $arrayAccess,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'countable',
            'value' => 'foo',
            'expectedValidity' => false,
            'expectedProcessedValue' => 'foo',
            'expectedProcessedValidity' => false,
        ];

        //
        // Callable
        //
        yield [
            'type' => 'callable',
            'value' => $intval,
            'expectedValidity' => true,
            'expectedProcessedValue' => $intval,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'callable',
            'value' => 'intval',
            'expectedValidity' => true,
            'expectedProcessedValue' => 'intval',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'callable',
            'value' => 'DateTime::createFromFormat',
            'expectedValidity' => true,
            'expectedProcessedValue' => 'DateTime::createFromFormat',
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'callable',
            'value' => ['DateTime', 'createFromFormat'],
            'expectedValidity' => true,
            'expectedProcessedValue' => ['DateTime', 'createFromFormat'],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'callable',
            'value' => 'functionDoesNotExist',
            'expectedValidity' => false,
            'expectedProcessedValue' => 'functionDoesNotExist',
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'callable',
            'value' => ['DateTime', 'methodDoesNotExist'],
            'expectedValidity' => false,
            'expectedProcessedValue' => ['DateTime', 'methodDoesNotExist'],
            'expectedProcessedValidity' => false,
        ];

        //
        // Arrays and collections
        //
        yield [
            'type' => 'array',
            'value' => [],
            'expectedValidity' => true,
            'expectedProcessedValue' => [],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'array',
            'value' => [1, 2, 3],
            'expectedValidity' => true,
            'expectedProcessedValue' => [1, 2, 3],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'array',
            'value' => $arrayIterator,
            'expectedValidity' => true,
            'expectedProcessedValue' => $arrayIterator,
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'array',
            'value' => 'test',
            'expectedValidity' => false,
            'expectedProcessedValue' => 'test',
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'array',
            'value' => null,
            'expectedValidity' => false,
            'expectedProcessedValue' => null,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string[]',
            'value' => [],
            'expectedValidity' => true,
            'expectedProcessedValue' => [],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string[]',
            'value' => ['foo', 'bar'],
            'expectedValidity' => true,
            'expectedProcessedValue' => ['foo', 'bar'],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string[]',
            'value' => [1, 'foo', 3],
            'expectedValidity' => false,
            'expectedProcessedValue' => [1, 'foo', 3],
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string[]',
            'value' => [$dateTime, 'foo'],
            'expectedValidity' => false,
            'expectedProcessedValue' => [$dateTime, 'foo'],
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'string[]',
            'value' => [$stringable, 'foo'],
            'expectedValidity' => true,
            'expectedProcessedValue' => [$stringable, 'foo'],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'string[]',
            'value' => null,
            'expectedValidity' => false,
            'expectedProcessedValue' => null,
            'expectedProcessedValidity' => false,
        ];
        // @todo should we check all items?
        yield [
            'type' => 'string[]',
            'value' => ['foo', 1, 2, 3],
            'expectedValidity' => true,
            'expectedProcessedValue' => ['foo', 1, 2, 3],
            'expectedProcessedValidity' => true,
        ];

        //
        // Union types
        //
        yield [
            'type' => 'array|string',
            'value' => ['foo', 1, 2, 3],
            'expectedValidity' => true,
            'expectedProcessedValue' => ['foo', 1, 2, 3],
            'expectedProcessedValidity' => true,
        ];
        yield [
            'type' => 'array|string',
            'value' => 'foo',
            'expectedValidity' => true,
            'expectedProcessedValue' => 'foo',
            'expectedProcessedValidity' => true,
        ];
        // No support for automatic type casting
        yield [
            'type' => 'array|string',
            'value' => 123,
            'expectedValidity' => false,
            'expectedProcessedValue' => 123,
            'expectedProcessedValidity' => false,
        ];
        yield [
            'type' => 'array|string',
            'value' => $dateTime,
            'expectedValidity' => false,
            'expectedProcessedValue' => $dateTime,
            'expectedProcessedValidity' => false,
        ];
    }

    #[Test]
    #[DataProvider('isValidAndProcessDataProvider')]
    public function isValidAndProcess(string $type, mixed $value, bool $expectedValidity, mixed $expectedProcessedValue, bool $expectedProcessedValidity): void
    {
        $argumentProcessor = new StrictArgumentProcessor();
        $definition = new ArgumentDefinition('test', $type, '', true);
        $processedValue = $argumentProcessor->process($value, $definition);
        self::assertSame($expectedValidity, $argumentProcessor->isValid($value, $definition));
        self::assertSame($expectedProcessedValue, $processedValue);
        self::assertSame($expectedProcessedValidity, $argumentProcessor->isValid($processedValue, $definition));
    }

    public static function optionalArgumentAllowsDefaultValueDataProvider(): array
    {
        return [
            ['string', null],
            ['object', null],
            ['DateTime', null],
            ['array', []],
            ['int[]', ['string']],
            ['array', 'my default'],
        ];
    }

    #[Test]
    #[DataProvider('optionalArgumentAllowsDefaultValueDataProvider')]
    public function optionalArgumentAllowsDefaultValue(string $type, mixed $defaultValue): void
    {
        $definition = new ArgumentDefinition('test', $type, '', false, $defaultValue);
        self::assertTrue((new StrictArgumentProcessor())->isValid($defaultValue, $definition));
    }
}
