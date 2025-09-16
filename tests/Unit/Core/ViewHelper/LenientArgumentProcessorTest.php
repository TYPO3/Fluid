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
use TYPO3Fluid\Fluid\Core\ViewHelper\LenientArgumentProcessor;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;

final class LenientArgumentProcessorTest extends TestCase
{
    public static function isValidAndProcessDataProvider(): array
    {
        // This list demonstrates the "lenient" behavior of the current argument processing.
        // Most scalar validations are wrong if the values aren't cast to the correct types
        // later. Also, the code relies on the BooleanParser as preprocessor and has a lot of
        // inconsistencies.
        return [
            [true, 'boolean', true],
            [false, 'boolean', true],
            [['bad'], 'boolean', false],
            // De-facto, all non-boolean values are converted by the parser
            [(new BooleanNode(123))->evaluate(new RenderingContext()), 'boolean', true],

            [true, 'bool', true],
            [false, 'bool', true],
            [['bad'], 'bool', true], // @todo this is clearly wrong
            // De-facto, all non-boolean values are converted by the parser
            [(new BooleanNode(123))->evaluate(new RenderingContext()), 'bool', true],

            [true, 'string', true],
            [false, 'string', true],
            [2, 'string', true],
            [1, 'string', true],
            [0, 'string', true],
            [-1, 'string', true],
            [1.5, 'string', true],
            ['', 'string', true],
            ['test', 'string', true],
            [null, 'string', true],
            [new stdClass(), 'string', false],
            [new \DateTime('now'), 'string', false],
            [[], 'string', true], // @todo this can lead to PHP warnings
            [['test'], 'string', true], // @todo this can lead to PHP warnings
            [new \ArrayIterator(['bar']), 'string', false],
            [new UserWithToString('foo'), 'string', true],

            [true, 'integer', true],
            [false, 'integer', true],
            [2, 'integer', true],
            [1, 'integer', true],
            [0, 'integer', true],
            [-1, 'integer', true],
            [1.5, 'integer', true],
            ['', 'integer', true],
            ['test', 'integer', true],
            [null, 'integer', true],
            [new stdClass(), 'integer', false],
            [new \DateTime('now'), 'integer', false],
            [[], 'integer', true], // @todo this can lead to PHP warnings
            [['test'], 'integer', true], // @todo this can lead to PHP warnings
            [new \ArrayIterator(['bar']), 'integer', false],
            [new UserWithToString('foo'), 'integer', false],

            [true, 'int', true],
            [false, 'int', true],
            [2, 'int', true],
            [1, 'int', true],
            [0, 'int', true],
            [-1, 'int', true],
            [1.5, 'int', true],
            ['', 'int', true],
            ['test', 'int', true],
            [null, 'int', true],
            [new stdClass(), 'int', false],
            [new stdClass(), 'int', false],
            [new \DateTime('now'), 'int', false],
            [[], 'int', true], // @todo this can lead to PHP warnings
            [['test'], 'int', true], // @todo this can lead to PHP warnings
            [new \ArrayIterator(['bar']), 'int', false],
            [new UserWithToString('foo'), 'int', false],

            [true, 'float', true],
            [false, 'float', true],
            [2, 'float', true],
            [1, 'float', true],
            [0, 'float', true],
            [-1, 'float', true],
            [1.5, 'float', true],
            ['', 'float', true],
            ['test', 'float', true],
            [null, 'float', true],
            [new stdClass(), 'float', false],
            [new stdClass(), 'float', false],
            [new \DateTime('now'), 'float', false],
            [[], 'float', true], // @todo this can lead to PHP warnings
            [['test'], 'float', true], // @todo this can lead to PHP warnings
            [new \ArrayIterator(['bar']), 'float', false],
            [new UserWithToString('foo'), 'float', false],

            [true, 'double', true],
            [false, 'double', true],
            [2, 'double', true],
            [1, 'double', true],
            [0, 'double', true],
            [-1, 'double', true],
            [1.5, 'double', true],
            ['', 'double', true],
            ['test', 'double', true],
            [null, 'double', true],
            [new stdClass(), 'double', false],
            [new stdClass(), 'double', false],
            [new \DateTime('now'), 'double', false],
            [[], 'double', true], // @todo this can lead to PHP warnings
            [['test'], 'double', true], // @todo this can lead to PHP warnings
            [new \ArrayIterator(['bar']), 'double', false],
            [new UserWithToString('foo'), 'double', false],

            // Union types just get silently ignored, unless an object is supplied
            [true, 'array|string', true],
            [false, 'array|string', true],
            [2, 'array|string', true],
            [1, 'array|string', true],
            [0, 'array|string', true],
            [-1, 'array|string', true],
            [1.5, 'array|string', true],
            ['', 'array|string', true],
            ['test', 'array|string', true],
            [null, 'array|string', true],
            [new stdClass(), 'array|string', false],
            [new stdClass(), 'array|string', false],
            [new \DateTime('now'), 'array|string', false],
            [[], 'array|string', true],
            [['test'], 'array|string', true],
            [new \ArrayIterator(['bar']), 'array|string', false],
            [new UserWithToString('foo'), 'array|string', false],

            [new \ArrayIterator(['bar']), 'DateTime', false],
            ['test', 'DateTime', false],
            [null, 'DateTime', true], // @todo this can lead to PHP warnings

            [new \Datetime('now'), 'DateTimeInterface', true],

            ['test', 'object', false],
            [null, 'object', false],

            [[], 'array', true],
            [[1, 2, 3], 'array', true],
            [new \ArrayObject(), 'array', true],

            [[], 'string[]', true],
            [['foo', 'bar'], 'string[]', true],
            [new \IteratorIterator(new \ArrayIterator(['foo', 'bar'])), 'string[]', true],
            [['foo', 1], 'string[]', true],
            [[1, 'foo', 2], 'string[]', true],
            [[new \DateTime('now'), 'test'], 'string[]', false],
            [[new UserWithToString('foo')], 'string[]', true],
        ];
    }

    #[Test]
    #[DataProvider('isValidAndProcessDataProvider')]
    public function isValidAndProcess(mixed $value, string $type, bool $expectedValidity): void
    {
        $definition = new ArgumentDefinition('test', $type, '', true);
        // Check validity
        self::assertSame($expectedValidity, (new LenientArgumentProcessor())->isValid($value, $definition));
        // Process should pass values unmodified
        self::assertSame($value, (new LenientArgumentProcessor())->process($value, $definition));
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
        self::assertTrue((new LenientArgumentProcessor())->isValid($defaultValue, $definition));
    }
}
