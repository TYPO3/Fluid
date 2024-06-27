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
                '<div />',
            ],
            'registered tag attribute' => [
                '<test:tagBasedTest registeredTagAttribute="test" />',
                '<div registeredTagAttribute="test" />',
            ],
            'unregistered argument' => [
                '<test:tagBasedTest foo="bar" />',
                '<div foo="bar" />',
            ],
            'data array' => [
                '<test:tagBasedTest data="{foo: \'bar\', more: 1}" />',
                '<div data-foo="bar" data-more="1" />',
            ],
            'aria array' => [
                '<test:tagBasedTest aria="{foo: \'bar\', more: 1}" />',
                '<div aria-foo="bar" aria-more="1" />',
            ],
            'data attribute' => [
                '<test:tagBasedTest data-foo="bar" />',
                '<div data-foo="bar" />',
            ],
            'aria attribute' => [
                '<test:tagBasedTest aria-foo="bar" />',
                '<div aria-foo="bar" />',
            ],
            'unregistered boolean argument false' => [
                '<test:tagBasedTest async="false" />',
                '<div />',
            ],
            'unregistered boolean argument true' => [
                '<test:tagBasedTest async="true" />',
                '<div async="async" />',
            ],
            'boolean argument false' => [
                '<test:tagBasedTest booleanArgument="false" />',
                '<div />',
            ],
            'boolean argument true' => [
                '<test:tagBasedTest booleanArgument="true" />',
                '<div booleanArgument="booleanArgument" />',
            ],
            'data array before data attribute' => [
                '<test:tagBasedTest data="{foo: \'data\'}" data-foo="attribute" />',
                '<div data-foo="attribute" />',
            ],
            'data array after data attribute' => [
                '<test:tagBasedTest data-foo="attribute" data="{foo: \'data\'}" />',
                '<div data-foo="attribute" />',
            ],
            'aria array before aria attribute' => [
                '<test:tagBasedTest aria="{foo: \'aria\'}" aria-foo="attribute" />',
                '<div aria-foo="attribute" />',
            ],
            'aria array after aria attribute' => [
                '<test:tagBasedTest aria-foo="attribute" aria="{foo: \'aria\'}" />',
                '<div aria-foo="attribute" />',
            ],
            'additional attributes' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'bar\'}" />',
                '<div data-foo="bar" />',
            ],
            'additional attributes and data array' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'additional\'}" data="{foo: \'data\'}" />',
                '<div data-foo="data" />',
            ],
            'additional attributes and data attribute' => [
                '<test:tagBasedTest additionalAttributes="{data-foo: \'additional\'}" data-foo="attribute" />',
                '<div data-foo="attribute" />',
            ],
            'override universal attribute' => [
                '<test:overrideUniversalAttributeTest />',
                '<div title="my default" />',
            ],
        ];
    }

    #[DataProvider('renderTagBasedViewHelperDataProvider')]
    #[Test]
    public function renderTagBasedViewHelper(string $source, string $expected): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output);

        // Second run to test cached template parsing
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $output = $view->render();
        self::assertEquals($expected, $output);
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
        $view->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\\Fluid\\Tests\\Functional\\Fixtures\\ViewHelpers');
        $view->render();
    }
}
