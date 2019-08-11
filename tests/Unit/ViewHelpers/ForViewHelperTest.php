<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper;

/**
 * Testcase for ForViewHelper
 */
class ForViewHelperTest extends ViewHelperBaseTestCase
{

    /**
     * @test
     */
    public function invalidObjectSubjectAsEachArgumentThrowsException(): void
    {
        $context = new RenderingContextFixture();
        $subject = new ForViewHelper();
        $subject->getArguments()->assignAll(['each' => new UserWithoutToString('user')]);
        $this->setExpectedException(Exception::class);
        $subject->evaluate($context);
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        return [
            'renders with null' => ['', $context, ['each' => null, 'as' => 'value'], [new ObjectAccessorNode('value')]],
            'renders each member value of an array' => ['foobarbaz', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value'], [new ObjectAccessorNode('value')]],
            'renders each member value of an array with reverse' => ['bazbarfoo', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'reverse' => true], [new ObjectAccessorNode('value')]],
            'renders each member value of an array with iterator data' => ['foo0bar1baz2', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'iteration' => 'i'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i.index')]],
            'renders each member value of an array with key' => ['foo0bar1baz2', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'key' => 'i'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i')]],
            'renders each member value of an array with iterator data and key' => ['foo00bar11baz22', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'iteration' => 'i', 'key' => 'key'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i.index'), new ObjectAccessorNode('key')]],
            'renders with traversable' => ['foobarbaz', $context, ['each' => new \ArrayIterator(['foo', 'bar', 'baz']), 'as' => 'foo'], [new ObjectAccessorNode('foo')]],
            'renders with traversable and reverse' => ['bazbarfoo', $context, ['each' => new \ArrayIterator(['foo', 'bar', 'baz']), 'as' => 'foo', 'reverse' => true], [new ObjectAccessorNode('foo')]],
        ];
    }
}
