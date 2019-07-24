<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper;

/**
 * Testcase for GroupedForViewHelper
 */
class GroupedForViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        $forViewHelper = new ForViewHelper();
        $forViewHelper->onOpen($context, $forViewHelper->getArguments()->assignAll(['each' => new ObjectAccessorNode('grouped'), 'as' => 'value']))->addChild(new ObjectAccessorNode('value.name'));
        return [
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
            'renders grouped elements from iterator with key' => [
                'z1x1y2',
                $context,
                ['each' => new \ArrayIterator([['prop' => 1, 'name' => 'z'], ['prop' => 2, 'name' => 'y'], ['prop' => 1, 'name' => 'x']]), 'as' => 'grouped', 'groupBy' => 'prop', 'groupKey' => 'key'],
                [(clone $forViewHelper)->addChild(new ObjectAccessorNode('key'))],
            ],
        ];
    }
}
