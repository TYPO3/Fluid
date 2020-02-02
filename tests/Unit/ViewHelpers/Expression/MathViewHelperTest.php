<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Expression;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\Expression\MathViewHelper;

/**
 * Testcase for Expression/MathViewHelper
 */
class MathViewHelperTest extends ViewHelperBaseTestCase
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
        $this->assertSame($expected, MathViewHelper::matches($parts));
    }

    public function getMatchesTestValues(): array
    {
        return [
            'plus' => [['var', '+', 3], true],
            'minus' => [['var', '-', 3], true],
            'multiply' => [['var', '*', 3], true],
            'modulo' => [['var', '%', 3], true],
            'divide' => [['var', '/', 3], true],
            'power' => [['var', '^', 3], true],
        ];
    }

    /**
     * @test
     * @dataProvider getEvaluateWithPartsTestValues
     * @param array $parts
     * @param int $expected
     */
    public function evaluateWithParts(array $parts, int $expected): void
    {
        $context = new RenderingContextFixture();
        $context->getVariableProvider()->add('var', 15);
        $subject = new MathViewHelper($parts);
        $this->assertSame($expected, $subject->evaluate($context));
    }

    public function getEvaluateWithPartsTestValues(): array
    {
        return [
            'plus' => [
                ['var', '+', 3],
                18,
            ],
            'minus' => [
                ['var', '-', 3],
                12,
            ],
        ];
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'plus' => [24, $context, ['a' => 12, 'operator' => '+', 'b' => 12]],
            'minus' => [6, $context, ['a' => 12, 'operator' => '-', 'b' => 6]],
            'multiply' => [144, $context, ['a' => 12, 'operator' => '*', 'b' => 12]],
            'divide' => [1, $context, ['a' => 12, 'operator' => '/', 'b' => 12]],
            'modulo' => [0, $context, ['a' => 12, 'operator' => '%', 'b' => 12]],
            'power' => [4, $context, ['a' => 2, 'operator' => '^', 'b' => 2]],
            'plus with toString a' => [2, $context, ['a' => new UserWithToString('user'), 'operator' => '+', 'b' => 2]],
            'plus with toString b' => [3, $context, ['a' => 3, 'operator' => '+', 'b' => new UserWithToString('user')]],
            'plus but a not compatible' => [2, $context, ['a' => new \DateTime('now'), 'operator' => '+', 'b' => 2]],
            'plus but b not compatible' => [2, $context, ['a' => 2, 'operator' => '+', 'b' => new \DateTime('now')]],
        ];
    }
}
