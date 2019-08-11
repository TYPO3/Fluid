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
use TYPO3Fluid\Fluid\ViewHelpers\GroupedForViewHelper;

/**
 * Testcase for GroupedForViewHelper
 */
class GroupedForViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     */
    public function invalidDataTypeInsideIteratorEachArgumentThrowsException(): void
    {
        $context = new RenderingContextFixture();
        $subject = new GroupedForViewHelper();
        $subject->getArguments()->assignAll(['each' => [fopen('/dev/null', 'r')], 'groupBy' => 'foo']);
        $this->setExpectedException(Exception::class);
        $subject->evaluate($context);
    }

    /**
     * @test
     */
    public function invalidObjectSubjectAsEachArgumentThrowsException(): void
    {
        $context = new RenderingContextFixture();
        $subject = new GroupedForViewHelper();
        $subject->getArguments()->assignAll(['each' => new UserWithoutToString('user')]);
        $this->setExpectedException(Exception::class);
        $subject->evaluate($context);
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        $forViewHelper = new ForViewHelper();
        $forViewHelper->onOpen($context, $forViewHelper->getArguments()->assignAll(['each' => new ObjectAccessorNode('grouped'), 'as' => 'value']))->addChild(new ObjectAccessorNode('value.name'));
        $user1 = new UserWithoutToString('user1');
        $user2 = new UserWithoutToString('user2');
        return [
            'returns empty string when subject is null' => [
                '',
                $context,
                ['each' => null],
            ],
            'renders grouped elements from array' => [
                'zxy',
                $context,
                ['each' => [['prop' => 1, 'name' => 'z'], ['prop' => 2, 'name' => 'y'], ['prop' => 1, 'name' => 'x']], 'as' => 'grouped', 'groupBy' => 'prop'],
                [$forViewHelper],
            ],
            'renders grouped elements from iterator' => [
                'zxy',
                $context,
                ['each' => new \ArrayIterator([['prop' => 1, 'name' => 'z'], ['prop' => 2, 'name' => 'y'], ['prop' => 1, 'name' => 'x']]), 'as' => 'grouped', 'groupBy' => 'prop'],
                [$forViewHelper],
            ],
            'renders grouped elements from array with key' => [
                'z1x1y2',
                $context,
                ['each' => [['prop' => 1, 'name' => 'z'], ['prop' => 2, 'name' => 'y'], ['prop' => 1, 'name' => 'x']], 'as' => 'grouped', 'groupBy' => 'prop', 'groupKey' => 'key'],
                [(clone $forViewHelper)->addChild(new ObjectAccessorNode('key'))],
            ],
            'renders grouped object elements from array with key' => [
                'alfredalfredbertha',
                $context,
                ['each' => [new UserWithoutToString('alfred'), new UserWithoutToString('alfred'), new UserWithoutToString('bertha')], 'as' => 'grouped', 'groupBy' => 'name', 'groupKey' => 'key'],
                [clone $forViewHelper],
            ],
            'renders grouped elements from iterator with key' => [
                'z1x1y2',
                $context,
                ['each' => new \ArrayIterator([['prop' => 1, 'name' => 'z'], ['prop' => 2, 'name' => 'y'], ['prop' => 1, 'name' => 'x']]), 'as' => 'grouped', 'groupBy' => 'prop', 'groupKey' => 'key'],
                [(clone $forViewHelper)->addChild(new ObjectAccessorNode('key'))],
            ],
            'supports grouping by DateTime' => [
                'zxy',
                $context,
                ['each' => [['prop' => new \DateTime('+1 day'), 'name' => 'z'], ['prop' => new \DateTime('+2 day'), 'name' => 'y'], ['prop' => new \DateTime('+1 day'), 'name' => 'x']], 'as' => 'grouped', 'groupBy' => 'prop', 'groupKey' => 'key'],
                [(clone $forViewHelper)->addChild(new ObjectAccessorNode('name'))],
            ],
            'supports grouping by object' => [
                'zxy',
                $context,
                ['each' => [['prop' => $user1, 'name' => 'z'], ['prop' => $user2, 'name' => 'y'], ['prop' => $user1, 'name' => 'x']], 'as' => 'grouped', 'groupBy' => 'prop', 'groupKey' => 'key'],
                [(clone $forViewHelper)->addChild(new ObjectAccessorNode('name'))],
            ],
        ];
    }
}
