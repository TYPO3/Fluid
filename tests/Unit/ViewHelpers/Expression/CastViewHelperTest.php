<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\CastViewHelper;

/**
 * Testcase for Expression/CastViewHelper
 */
class CastViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     * @dataProvider getMatchesTestValues
     * @param array $parts
     * @param bool|null $expected
     */
    public function matchesWorksAsExpected(array $parts, ?bool $expected): void
    {
        if ($expected === null) {
            $this->setExpectedException(ExpressionException::class);
        }
        $this->assertSame($expected, CastViewHelper::matches($parts));
    }

    public function getMatchesTestValues(): array
    {
        return [
            'works with string cast' => [['foo', 'as', 'string'], true],
            'works with array cast' => [['foo', 'as', 'array'], true],
            'works with integer cast' => [['foo', 'as', 'integer'], true],
            'works with boolean cast' => [['foo', 'as', 'boolean'], true],
            'works with float cast' => [['foo', 'as', 'float'], true],
            'works with DateTime cast' => [['foo', 'as', 'DateTime'], true],
            'errors with invalid type' => [['foo', 'as', 'invalid'], null],
        ];
    }

    /**
     * @test
     * @dataProvider getEvaluateWithPartsTestValues
     * @param array $parts
     * @param mixed $expected
     */
    public function evaluateWithParts(array $parts, $expected): void
    {
        $context = new RenderingContextFixture();
        $context->getVariableProvider()->add('foo', 'foostring');
        $subject = new CastViewHelper($parts);
        $this->assertSame($expected, $subject->evaluate($context));
    }

    public function getEvaluateWithPartsTestValues(): array
    {
        return [
            'to string' => [
                ['foo', 'as', 'string'],
                'foostring',
            ],
            'to array' => [
                ['foo', 'as', 'array'],
                ['foostring'],
            ],
        ];
    }

    public function getStandardTestValues(): array
    {
        $timestamp = time();
        $expectedDateTime1 = \DateTime::createFromFormat('U', (string) $timestamp);
        $expectedDateTime2 = new \DateTime('2019-05-10T11:11');
        $context = new RenderingContextFixture();
        return [
            'cast to string' => ['3', $context, ['subject' => 3, 'as' => 'string']],
            'cast to integer' => [123, $context, ['subject' => '123', 'as' => 'integer']],
            'cast to boolean' => [true, $context, ['subject' => 1, 'as' => 'boolean']],
            'cast to float' => [1.23, $context, ['subject' => '1.23', 'as' => 'float']],
            'cast to DateTime from int' => [$expectedDateTime1, $context, ['subject' => $timestamp, 'as' => 'DateTime']],
            'cast to DateTime from string' => [$expectedDateTime2, $context, ['subject' => '2019-05-10T11:11', 'as' => 'DateTime']],
            'cast to array with array' => [['foo'], $context, ['subject' => ['foo'], 'as' => 'array']],
            'cast to array with string' => [['foo'], $context, ['subject' => 'foo', 'as' => 'array']],
            'cast to array with boolean false' => [[], $context, ['subject' => false, 'as' => 'array']],
            'cast to array with boolean true' => [[], $context, ['subject' => true, 'as' => 'array']],
            'cast to array with CSV string' => [['foo', 'bar'], $context, ['subject' => 'foo,bar', 'as' => 'array']],
            'cast to array with iterator' => [['foo'], $context, ['subject' => new \ArrayIterator(['foo']), 'as' => 'array']],
            'cast to array with toArray' => [['name' => 'foo'], $context, ['subject' => new UserWithoutToString('foo'), 'as' => 'array']],
        ];
    }
}
