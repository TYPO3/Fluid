<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\UnsafeHTMLString;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithToString;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ConditionViewHelperTest extends AbstractFunctionalTestCase
{
    public static function basicConditionDataProvider(): array
    {
        return [
            ['1 == 1', true],
            ['1 != 2', true],
            ['1 == 2', false],
            ['1 === 1', true],
            ['\'foo\' == 0', false],
            ['1.1 >= \'foo\'', false],
            ['\'String containing word \"false\" in text\'', true],
            ['\'  FALSE  \'', true],
            ['\'foo\' > 0', true],
            ['FALSE', false],
            ['(FALSE || (FALSE || 1)', true],
            ['(FALSE or (FALSE or 1)', true],

            // integers
            ['13 == \'13\'', true],
            ['13 === \'13\'', false],

            // floats
            ['13.37 == \'13.37\'', true],
            ['13.37 === \'13.37\'', false],

            // groups
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1', true],
            ['(1 && (\'foo\' == \'foo\') && (TRUE || 1)) && 0 != 1 && FALSE', false],
        ];
    }

    #[DataProvider('basicConditionDataProvider')]
    #[Test]
    public function basicCondition(string $source, bool $expected): void
    {
        $source = '<f:if condition="' . $source . '" then="yes" else="no" />';
        $expected = $expected === true ? 'yes' : 'no';

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        self::assertSame($expected, $view->render());
    }

    public static function variableConditionDataProvider(): array
    {
        $user1 = new UserWithToString('foobar');
        $user2 = new UserWithToString('foobar');
        $someObject = new \stdClass();
        $someObject->someString = 'bar';
        $someObject->someInt = 1337;
        $someObject->someFloat = 13.37;
        $someObject->someBoolean = true;
        $someArray = [
            'foo' => 'bar',
        ];
        $emptyCountable = new \SplObjectStorage();
        $htmlString = new UnsafeHTMLString('baz');
        $emptyHtmlString = new UnsafeHTMLString('');

        return [
            // simple assignments
            ['{test}', true, ['test' => 1]],
            ['{test}', true, ['test' => '\'  FALSE  \'']],
            ['{test}', true, ['test' => '\'  0  \'']],
            ['{test}', false, ['test' => 0]],
            ['!{test}', true, ['test' => false]],
            ['!{test}', false, ['test' => true]],
            ['1 == {test}', true, ['test' => 1]],
            ['1 != {test}', true, ['test' => 2]],
            ['{test1} == {test2}', true, ['test1' => 'abc', 'test2' => 'abc']],
            ['{test1} === {test2}', true, ['test1' => 'abc', 'test2' => 'abc']],
            ['{test1} === {test2}', false, ['test1' => 1, 'test2' => true]],
            ['{test1} == {test2}', true, ['test1' => 1, 'test2' => true]],

            // conditions with UnsafeHTMLString
            ['{test}', true, ['test' => $htmlString]],
            ['!{test}', false, ['test' => $htmlString]],

            ['{test} == \'baz\'', true, ['test' => $htmlString]],
            ['{test1} === {test2}', false, ['test1' => 'baz', 'test2' => $htmlString]],
            ['{test1} == {test2}', true, ['test1' => 'baz', 'test2' => $htmlString]],
            ['{test} != \'baz\'', false, ['test' => $htmlString]],
            ['{test1} !== {test2}', true, ['test1' => 'baz', 'test2' => $htmlString]],
            ['{test1} != {test2}', false, ['test1' => 'baz', 'test2' => $htmlString]],

            ['{test1} < {test2}', true, ['test1' => 'a', 'test2' => $htmlString]],
            ['{test1} < {test2}', false, ['test1' => 'z', 'test2' => $htmlString]],
            ['{test1} < {test2}', false, ['test1' => 'baz', 'test2' => $htmlString]],
            ['{test1} > {test2}', false, ['test1' => 'a', 'test2' => $htmlString]],
            ['{test1} > {test2}', true, ['test1' => 'z', 'test2' => $htmlString]],
            ['{test1} > {test2}', false, ['test1' => 'baz', 'test2' => $htmlString]],

            ['{test1} <= {test2}', true, ['test1' => 'a', 'test2' => $htmlString]],
            ['{test1} <= {test2}', false, ['test1' => 'z', 'test2' => $htmlString]],
            ['{test1} <= {test2}', true, ['test1' => 'baz', 'test2' => $htmlString]],
            ['{test1} >= {test2}', false, ['test1' => 'a', 'test2' => $htmlString]],
            ['{test1} >= {test2}', true, ['test1' => 'z', 'test2' => $htmlString]],
            ['{test1} >= {test2}', true, ['test1' => 'baz', 'test2' => $htmlString]],

            ['{test1} % {test2}', false, ['test1' => '0', 'test2' => new UnsafeHTMLString('2')]],
            ['{test1} % {test2}', true, ['test1' => '1', 'test2' => new UnsafeHTMLString('2')]],
            ['{test1} % {test2}', false, ['test1' => '2', 'test2' => new UnsafeHTMLString('2')]],
            ['{test1} % {test2}', true, ['test1' => '3', 'test2' => new UnsafeHTMLString('2')]],
            ['{test1} % {test2}', false, ['test1' => '4', 'test2' => new UnsafeHTMLString('2')]],

            ['{test}', false, ['test' => $emptyHtmlString]],
            ['!{test}', true, ['test' => $emptyHtmlString]],
            ['{test1} || {test2}', false, ['test1' => $emptyHtmlString, 'test2' => '']],
            ['{test1} || {test2}', false, ['test1' => $emptyHtmlString, 'test2' => $emptyHtmlString]],
            ['{test1} || {test2}', false, ['test1' => '', 'test2' => $emptyHtmlString]],
            ['{test1} || {test2}', true, ['test1' => $htmlString, 'test2' => '']],
            ['{test1} || {test2}', true, ['test1' => '', 'test2' => $htmlString]],

            ['{test1} && {test2}', false, ['test1' => $emptyHtmlString, 'test2' => '']],
            ['{test1} && {test2}', false, ['test1' => $emptyHtmlString, 'test2' => $emptyHtmlString]],
            ['{test1} && {test2}', false, ['test1' => '', 'test2' => $emptyHtmlString]],

            ['{test1} && {test2}', true, ['test1' => $htmlString, 'test2' => 'abc']],
            ['{test1} && {test2}', true, ['test1' => $htmlString, 'test2' => $htmlString]],
            ['{test1} && {test2}', true, ['test1' => 'abc', 'test2' => $htmlString]],

            // conditions with objects
            ['{user1} == {user1}', true, ['user1' => $user1]],
            ['{user1} === {user1}', true, ['user1' => $user1]],
            // @todo: This breaks for compiled / cached templates. Needs investigation. Parser bug, or a side effect of stringable UserWithToString??
            // ['{user1} == {user2}', false, ['user1' => $user1, 'user2' => $user2]],
            ['{user1} === {user2}', false, ['user1' => $user1, 'user2' => $user2]],

            // conditions with object properties
            ['{someObject.someString} == \'bar\'', true, ['someObject' => $someObject]],
            ['{someObject.someString} === \'bar\'', true, ['someObject' => $someObject]],

            ['{someObject.someInt} == \'1337\'', true, ['someObject' => $someObject]],
            ['{someObject.someInt} === \'1337\'', false, ['someObject' => $someObject]],
            ['{someObject.someInt} === 1337', true, ['someObject' => $someObject]],

            ['{someObject.someFloat} == \'13.37\'', true, ['someObject' => $someObject]],
            ['{someObject.someFloat} === \'13.37\'', false, ['someObject' => $someObject]],
            ['{someObject.someFloat} === 13.37', true, ['someObject' => $someObject]],

            ['{someObject.someBoolean} == 1', true, ['someObject' => $someObject]],
            ['{someObject.someBoolean} === 1', false, ['someObject' => $someObject]],
            ['{someObject.someBoolean} == TRUE', true, ['someObject' => $someObject]],
            ['{someObject.someBoolean} === TRUE', true, ['someObject' => $someObject]],

            // array conditions
            ['{someArray} == {foo: \'bar\'}', true, ['someArray' => $someArray]],
            ['{someArray} === {foo: \'bar\'}', true, ['someArray' => $someArray]],
            ['{someArray.foo} == \'bar\'', true, ['someArray' => $someArray]],
            ['({someArray.foo} == \'bar\') && (TRUE || 0)', true, ['someArray' => $someArray]],
            ['({foo.someArray.foo} == \'bar\') && (TRUE || 0)', true, ['foo' => ['someArray' => $someArray]]],

            // inline viewHelpers
            ['(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 1)', true, []],
            ['(TRUE && ({f:if(condition: \'TRUE\', then: \'1\')} == 0)', false, []],

            // conditions with countable objects
            ['{emptyCountable}', false, ['emptyCountable' => $emptyCountable]],
            ['FALSE || FALSE', false, ['emptyCountable' => $emptyCountable]],
            ['{emptyCountable} || FALSE', false, ['emptyCountable' => $emptyCountable]],
            ['FALSE || {emptyCountable}', false, ['emptyCountable' => $emptyCountable]],
            // inline if-viewhelper condition with countable objects
            ['{f:if(condition: \'{emptyCountable} || FALSE\', else: \'1\')} == 1)', true, ['emptyCountable' => $emptyCountable]],
        ];
    }

    #[DataProvider('variableConditionDataProvider')]
    #[Test]
    public function variableCondition(string $source, bool $expected, array $variables): void
    {
        $source = '<f:if condition="' . $source . '" then="yes" else="no" />';
        $expected = $expected === true ? 'yes' : 'no';

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
