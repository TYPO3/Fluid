<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Conditions;

use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class TernaryConditionsTest extends AbstractFunctionalTestCase
{
    public function variableConditionDataProvider(): array
    {
        return [
            [
                '{true ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            [
                '{true ? 1 : 2}',
                [],
                '1',
            ],
            [
                '{true ? foo : \'bar\'}',
                ['foo' => 'bar'],
                'bar',
            ],
            [
                '{(true) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            [
                '{(true || false) ? \'yes\' : \'no\'}',
                [],
                'yes',
            ],
            [
                '{(false || false) ? \'yes\' : \'no\'}',
                [],
                'no',
            ],
            [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => true],
                'yes',
            ],
            [
                '{foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                'no',
            ],
            [
                '{!foo ? \'yes\' : \'no\'}',
                ['foo' => false],
                'yes',
            ],
            [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => true],
                'yes',
            ],
            [
                '{(foo || false) ? \'yes\' : \'no\'}',
                ['foo' => false],
                'no',
            ],
            [
                '{(foo.bar || false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                'yes',
            ],
            [
                '{(foo.bar && false) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => true]],
                'no',
            ],
            [
                '{(foo.bar > 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'yes',
            ],
            [
                '{(foo.bar < 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'no',
            ],
            [
                '{(foo.bar < 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'no',
            ],
            [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 11]],
                'yes',
            ],
            [
                '{(foo.bar % 10) ? \'yes\' : \'no\'}',
                ['foo' => ['bar' => 10]],
                'no',
            ],
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
