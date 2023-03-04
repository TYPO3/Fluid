<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

use TYPO3Fluid\Fluid\Core\Parser\BooleanParser;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class BooleanParserTest extends UnitTestCase
{
    public static function getSomeEvaluationTestValues(): array
    {
        return [
            ['(1 && false) || false || \'foobar\' == \'foobar\'', true],

            ['0', false],
            ['!(1)', false],
            ['!1', false],
            ['', false],
            ['false', false],
            ['false || false', false],
            ['FALSE', false],
            ['fAlSe', false],
            ['   false   ', false],
            ['   FALSE   ', false],
            ['     ', false],
            ['\'foo\' == \'bar\'', false],
            ['\'foo\' != \'foo\'', false],

            ['1', true],
            ['true', true],
            ['TRUE', true],
            ['tRuE', true],
            ['   true   ', true],
            ['   TRUE   ', true],
            ['\' FALSE \'', true],
            ['\' \\\'FALSE \'', true],
            ['\' \\"FALSE \'', true],
            ['foo', true],
            ['\'foo\' == \'foo\'', true],
            ['\'foo\' != \'bar\'', true],
            ['(1 && false) || false || \'foobar\' == \'foobar\'', true],

            ['0 == \'0\'', true, []],
            ['0 == "0"', true, []],
            ['0 === \'0\'', false, []],

            ['1 == 1', true],
            ['1 == 0', false],
            ['1 >= 1', true],
            ['1 <= 1', true],
            ['1 >= 2', false],
            ['2 <= 1', false],
            ['-1 != -1', false],
            ['-1 == -1', true],
            ['-1 < 0', true],
            ['-1 > -2', true],

            ['1 > FALSE',  true],
            ['FALSE > 0',  false],

            ['2 % 2', false],
            ['1 % 2', true],

            ['0 && 1', false],
            ['1 && 1', true],
            ['0 || 0', false],
            ['0 || 1', true],
            ['(0 && 1) || 1', true],
            ['(0 && 0) || 0', false],
            ['(1 && 1) || 0', true],

            ['0 and 1', false],
            ['1 and 1', true],
            ['0 or 0', false],
            ['0 or 1', true],
            ['(0 and 1) or 1', true],
            ['(0 and 0) or 0', false],
            ['(1 and 1) or 0', true],
            ['0 And 1', false],
            ['1 anD 1', true],
            ['0 oR 0', false],
            ['0 Or 1', true],
            ['0 AND 1', false],
            ['1 AND 1', true],
            ['0 OR 0', false],
            ['0 OR 1', true],

            // edge cases as per https://github.com/TYPO3Fluid/Fluid/issues/7
            // expected value based on php versions behaviour
            ['\'foo\' == 0', (PHP_VERSION_ID < 80000 ? true : false)],
            ['1.1 >= foo', (PHP_VERSION_ID < 80000 ? true : false)],
            ['\'foo\' > 0', (PHP_VERSION_ID < 80000 ? false : true)],

            ['{foo}', true, ['foo' => true]],
            ['{foo} == FALSE', true, ['foo' => false]],
            ['!{foo}', true, ['foo' => false]]
        ];
    }

    /**
     * @test
     * @dataProvider getSomeEvaluationTestValues
     */
    public function testSomeEvaluations(string $comparison, bool $expected, array $variables = []): void
    {
        $renderingContext = new RenderingContextFixture();
        $parser = new BooleanParser();
        self::assertEquals($expected, BooleanNode::convertToBoolean($parser->evaluate($comparison, $variables), $renderingContext), 'Expression: ' . $comparison);
        $compiledEvaluation = $parser->compile($comparison);
        $functionName = 'expression_' . md5($comparison . rand(0, 100000));
        eval('function ' . $functionName . '($context) {return ' . $compiledEvaluation . ';}');
        self::assertEquals($expected, BooleanNode::convertToBoolean($functionName($variables), $renderingContext), 'compiled Expression: ' . $compiledEvaluation);
    }
}
