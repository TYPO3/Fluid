<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser\SyntaxTree\Expression;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class TernaryExpressionNodeTest extends AbstractFunctionalTestCase
{
    public static function variableConditionDataProvider(): array
    {
        return [
            'true evaluates to then' => [
                '{true ? yes : no}',
                [],
                'yes',
            ],
            'false evaluates to else' => [
                '{false ? yes : no}',
                [],
                'no',
            ],

            'one int evaluates to then' => [
                '{1 ? yes : no}',
                [],
                'yes',
            ],
            'zero int evaluates to else' => [
                '{0 ? yes : no}',
                [],
                'no',
            ],

            'true negated evaluates to else' => [
                '{!true ? yes : no}',
                [],
                'no',
            ],
            'false negated evaluates to then' => [
                '{!false ? yes : no}',
                [],
                'yes',
            ],

            'true evaluates to then string' => [
                '{true ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'false evaluates to else string' => [
                '{false ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'true evaluates to then integer' => [
                '{true ? 1 : 0}',
                [],
                '1',
            ],
            'false evaluates to else integer' => [
                '{false ? 1 : 0}',
                [],
                '0',
            ],

            'true evaluates then variable' => [
                '{true ? foo : \'bar\'}',
                ['foo' => 'baz'],
                'baz',
            ],
            'false evaluates else variable' => [
                '{false ? foo : bar}',
                ['bar' => 'baz'],
                'baz',
            ],

            'true in round brackets evaluates to then' => [
                '{(true) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'false in round brackets evaluates to else' => [
                '{(false) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'true in negated round brackets evaluates to else' => [
                '{!(true) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],
            'false in negated round brackets evaluates to then' => [
                '{!(false) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],

            'true or false evaluates to then' => [
                '{(true || false) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'true or false negated evaluates to else' => [
                '{!(true || false) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'false or false evaluates to else' => [
                '{(false || false) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],
            'false or false negated evaluates to then' => [
                '{!(false || false) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],

            'variable set to true evaluates to true' => [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => true],
                'yes',
            ],
            'variable set to false evaluates to true' => [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                'no',
            ],

            'variable negated set to true evaluates to true' => [
                '{!foo ? \'yes\' : \'no\'}',
                ['foo' => true],
                'no',
            ],
            'variable negated set to false evaluates to true' => [
                '{!foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                'yes',
            ],

            'true && 1 evaluates to else' => [
                '{(false && 1) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],
            'true && 1 negated evaluates to then' => [
                '{!(false && 1) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],

            'variable set to true or\'ed with false evaluates to then' => [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => true],
                'yes',
            ],
            'variable set to false or\'ed with false evaluates to else' => [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => false],
                'no',
            ],

            'variable array nested set to true or\'ed with false evaluates to then' => [
                '{(foo.bar || false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                'yes',
            ],
            'variable array nested set to true and\'ed with false evaluates to else' => [
                '{(foo.bar && false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                'no',
            ],

            'simple greater than with int\'s evaluates to then' => [
                '{(1 > 0) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'simple smaller than with int\'s evaluates to else' => [
                '{(1 < 0) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'variable array nested set to int with greater than comparator evaluates to then' => [
                '{(foo.bar > 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'yes',
            ],
            'variable array nested set to int with greater than comparator evaluates to else' => [
                '{(foo.bar < 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'no',
            ],

            'simple greater than equals with int\'s evaluates to then' => [
                '{(1 >= 0) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'simple smaller than equals with int\'s evaluates to else' => [
                '{(1 <= 0) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'variable array nested set to int with modulo operator evaluates to then' => [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'yes',
            ],
            'variable array nested set to int with modulo operator evaluates to else' => [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 10]],
                'no',
            ],

            'comparing two identical strings evaluates to then' => [
                '{(\'foo\' == \'foo\') ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            'comparing two different strings evaluates to else' => [
                '{(\'foo\' == \'bar\') ? \'yes\' : \'no\'}',
                [],
                'no',
            ],

            'complex expression evaluates to then' => [
                '{(foo || 1 && 1 && !(false) || (1 % 2) || (1 > 0) || (\'foo\' == \'bar\')) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ]
        ];
    }

    /**
     * @test
     * @dataProvider variableConditionDataProvider
     */
    public function variableCondition(string $source, array $variables, $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());
    }
}
