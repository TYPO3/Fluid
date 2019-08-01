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

/**
 * Testcase for ForViewHelper
 */
class ForViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setVariableProvider(new StandardVariableProvider());
        return [
            'renders each member value of an array' => ['foobarbaz', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value'], [new ObjectAccessorNode('value')]],
            'renders each member value of an array with reverse' => ['bazbarfoo', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'reverse' => true], [new ObjectAccessorNode('value')]],
            'renders each member value of an array with iterator data' => ['foo0bar1baz2', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'iteration' => 'i'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i.index')]],
            'renders each member value of an array with key' => ['foo0bar1baz2', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'key' => 'i'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i')]],
            'renders each member value of an array with iterator data and key' => ['foo00bar11baz22', $context, ['each' => ['foo', 'bar', 'baz'], 'as' => 'value', 'iteration' => 'i', 'key' => 'key'], [new ObjectAccessorNode('value'), new ObjectAccessorNode('i.index'), new ObjectAccessorNode('key')]],
        ];
    }
}
