<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\ViewHelpers\CycleViewHelper;

/**
 * Testcase for CycleViewHelper
 */
class CycleViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     */
    public function throwsExceptionOnIncompatibleObjectValue(): void
    {
        $context = new RenderingContextFixture();
        $subject = new CycleViewHelper();
        $subject->getArguments()->assignAll(['values' => new UserWithToString('user')]);
        $this->setExpectedException(Exception::class);
        $subject->evaluate($context);
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $context->setViewHelperVariableContainer(new ViewHelperVariableContainer());
        $context->setVariableProvider(new StandardVariableProvider());
        return [
            'renders cycled variables' => ['foobarbaz', $context, ['values' => ['foo', 'bar', 'baz'], 'as' => 'value'], [new ObjectAccessorNode('value')], 3],
            'renders cycled variables in array iterator' => ['foobarbaz', $context, ['values' => new \ArrayIterator(['foo', 'bar', 'baz']), 'as' => 'value'], [new ObjectAccessorNode('value')], 3],
            'renders child content when variables are null' => ['foofoofoo', $context, ['values' => null, 'as' => 'value'], [new TextNode('foo')], 3],
        ];
    }
}
