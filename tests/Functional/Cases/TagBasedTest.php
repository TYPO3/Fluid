<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class TagBasedTest extends AbstractFunctionalTestCase
{
    public static function renderTagBasedViewHelperDataProvider(): array
    {
        return [
            'registered argument' => [
                '<test:tagBasedTest registeredArgument="test" />',
                "{test:tagBasedTest(registeredArgument: 'test')}",
                [],
                '<div />',
            ],

            // Arguments that are explicitly defined with type boolean
            // still retain the original boolean behavior:
            // string input is interpreted in a way that "true" equals true
            // and "false" equals false
            'string as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="test" />',
                "{test:tagBasedTest(registeredBooleanArgument: 'test')}",
                [],
                '<div registeredBooleanArgument="registeredBooleanArgument" />',
            ],
            'string true as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="true" />',
                "{test:tagBasedTest(registeredBooleanArgument: 'true')}",
                [],
                '<div registeredBooleanArgument="registeredBooleanArgument" />',
            ],
            'string false as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="false" />',
                "{test:tagBasedTest(registeredBooleanArgument: 'false')}",
                [],
                '<div />',
            ],
            'string null as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="null" />',
                "{test:tagBasedTest(registeredBooleanArgument: 'null')}",
                [],
                '<div registeredBooleanArgument="registeredBooleanArgument" />', // @todo this should probably behave differently
            ],
            'empty registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="" />',
                "{test:tagBasedTest(registeredBooleanArgument: '')}",
                [],
                '<div />',
            ],
            'variable with true as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{var}" />',
                '{test:tagBasedTest(registeredBooleanArgument: var)}',
                ['var' => true],
                '<div registeredBooleanArgument="registeredBooleanArgument" />',
            ],
            'variable with false as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{var}" />',
                '{test:tagBasedTest(registeredBooleanArgument: var)}',
                ['var' => false],
                '<div />',
            ],
            'variable with null as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{var}" />',
                '{test:tagBasedTest(registeredBooleanArgument: var)}',
                ['var' => null],
                '<div />',
            ],
            'undefined variable as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{var}" />',
                '{test:tagBasedTest(registeredBooleanArgument: var)}',
                [],
                '<div />',
            ],
            'casted variable as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{var as boolean}" />',
                '{test:tagBasedTest(registeredBooleanArgument: \'{var as boolean}\')}',
                ['var' => '0'],
                '<div />',
            ],
            'boolean literal true as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{true}" />',
                '{test:tagBasedTest(registeredBooleanArgument: true)}',
                [],
                '<div registeredBooleanArgument="registeredBooleanArgument" />',
            ],
            'boolean literal false as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{false}" />',
                '{test:tagBasedTest(registeredBooleanArgument: false)}',
                [],
                '<div />',
            ],
            'null literal as registered bool attribute' => [
                '<test:tagBasedTest registeredBooleanArgument="{null}" />',
                '{test:tagBasedTest(registeredBooleanArgument: null)}',
                [],
                '<div />',
            ],

            // Unregistered ViewHelper arguments take strings as-is. To
            // create a boolean argument, the passed value needs to have
            // the correct type, either boolean or null
            'string as unregistered argument' => [
                '<test:tagBasedTest foo="bar" />',
                "{test:tagBasedTest(foo: 'bar')}",
                [],
                '<div foo="bar" />',
            ],
            'string true as unregistered argument' => [
                '<test:tagBasedTest foo="true" />',
                "{test:tagBasedTest(foo: 'true')}",
                [],
                '<div foo="true" />',
            ],
            'string false as unregistered argument' => [
                '<test:tagBasedTest foo="false" />',
                "{test:tagBasedTest(foo: 'false')}",
                [],
                '<div foo="false" />',
            ],
            'string null as unregistered argument' => [
                '<test:tagBasedTest foo="null" />',
                "{test:tagBasedTest(foo: 'null')}",
                [],
                '<div foo="null" />',
            ],
            'empty unregistered argument' => [
                '<test:tagBasedTest foo="" />',
                "{test:tagBasedTest(foo: '')}",
                [],
                '<div />', // @todo this should render an empty attribute, however this would be a breaking change in templates
            ],
            'variable with true as unregistered argument' => [
                '<test:tagBasedTest foo="{var}" />',
                '{test:tagBasedTest(foo: var)}',
                ['var' => true],
                '<div foo="foo" />',
            ],
            'variable with false as unregistered argument' => [
                '<test:tagBasedTest foo="{var}" />',
                '{test:tagBasedTest(foo: var)}',
                ['var' => false],
                '<div />',
            ],
            'variable with null as unregistered argument' => [
                '<test:tagBasedTest foo="{var}" />',
                '{test:tagBasedTest(foo: var)}',
                ['var' => null],
                '<div />',
            ],
            'undefined variable as unregistered argument' => [
                '<test:tagBasedTest foo="{var}" />',
                '{test:tagBasedTest(foo: var)}',
                [],
                '<div />',
            ],
            'casted variable as unregistered argument' => [
                '<test:tagBasedTest foo="{var as boolean}" />',
                '{test:tagBasedTest(foo: \'{var as boolean}\')}',
                ['var' => '0'],
                '<div />',
            ],
            'boolean literal true as unregistered argument' => [
                '<test:tagBasedTest async="{true}" />',
                '{test:tagBasedTest(async: true)}',
                [],
                '<div async="async" />',
            ],
            'boolean literal false as unregistered argument' => [
                '<test:tagBasedTest async="{false}" />',
                '{test:tagBasedTest(async: false)}',
                [],
                '<div />',
            ],
            'null literal as unregistered argument' => [
                '<test:tagBasedTest async="{null}" />',
                '{test:tagBasedTest(async: null)}',
                [],
                '<div />',
            ],

            'data array' => [
                '<test:tagBasedTest data="{foo: \'bar\', more: 1}" />',
                '{test:tagBasedTest(data: {foo: \'bar\', more: 1})}',
                [],
                '<div data-foo="bar" data-more="1" />',
            ],
            'aria array' => [
                '<test:tagBasedTest aria="{foo: \'bar\', more: 1}" />',
                '{test:tagBasedTest(aria: {foo: \'bar\', more: 1})}',
                [],
                '<div aria-foo="bar" aria-more="1" />',
            ],
            'data attribute' => [
                '<test:tagBasedTest data-foo="bar" />',
                '{test:tagBasedTest(data-foo: \'bar\')}',
                [],
                '<div data-foo="bar" />',
            ],
            'aria attribute' => [
                '<test:tagBasedTest aria-foo="bar" />',
                '{test:tagBasedTest(aria-foo: \'bar\')}',
                [],
                '<div aria-foo="bar" />',
            ],
            'data array before data attribute' => [
                '<test:tagBasedTest data="{foo: \'data\'}" data-foo="attribute" />',
                '{test:tagBasedTest(data: {foo: \'data\'}, data-foo: \'attribute\')}',
                [],
                '<div data-foo="attribute" />',
            ],
            'data array after data attribute' => [
                '<test:tagBasedTest data-foo="attribute" data="{foo: \'data\'}" />',
                '{test:tagBasedTest(data-foo: \'attribute\', data: {foo: \'data\'})}',
                [],
                '<div data-foo="attribute" />',
            ],
            'aria array before aria attribute' => [
                '<test:tagBasedTest aria="{foo: \'aria\'}" aria-foo="attribute" />',
                '{test:tagBasedTest(aria: {foo: \'aria\'}, aria-foo: \'attribute\')}',
                [],
                '<div aria-foo="attribute" />',
            ],
            'aria array after aria attribute' => [
                '<test:tagBasedTest aria-foo="attribute" aria="{foo: \'aria\'}" />',
                '{test:tagBasedTest(aria-foo: \'attribute\', aria: {foo: \'aria\'})}',
                [],
                '<div aria-foo="attribute" />',
            ],
            'additional attributes' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'bar\'}" />',
                '{test:tagBasedTest(additionalAttributes: {data-foo: \'bar\'})}',
                [],
                '<div data-foo="bar" />',
            ],
            'additional attributes and data array' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'additional\'}" data="{foo: \'data\'}" />',
                '{test:tagBasedTest(additionalAttributes: {data-foo: \'additional\'}, data: {foo: \'data\'})}',
                [],
                '<div data-foo="data" />',
            ],
            'additional attributes and data attribute' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'additional\'}" data-foo="attribute" />',
                '{test:tagBasedTest(additionalAttributes: {data-foo: \'additional\'}, data-foo: \'attribute\')}',
                [],
                '<div data-foo="attribute" />',
            ],
        ];
    }

    #[DataProvider('renderTagBasedViewHelperDataProvider')]
    #[Test]
    public function renderTagBasedViewHelper(string $source, string $sourceInline, array $variables, string $expected): void
    {
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output, 'tag variant uncached');

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($sourceInline);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output, 'inline variant uncached');

        // Second run to test cached template parsing
        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output, 'tag variant cached');

        $view = new TemplateView();
        $view->assignMultiple($variables);
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($sourceInline);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output, 'inline variant cached');
    }

    public static function throwsErrorForInvalidArgumentTypesDatProvider(): array
    {
        return [
            'data argument as string' => [
                '<test:tagBasedTest data="test" />',
            ],
            'aria argument as string' => [
                '<test:tagBasedTest aria="test" />',
            ],
        ];
    }

    #[DataProvider('throwsErrorForInvalidArgumentTypesDatProvider')]
    #[Test]
    public function throwsErrorForInvalidArgumentTypes(string $source): void
    {
        self::expectException(\InvalidArgumentException::class);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();
    }
}
