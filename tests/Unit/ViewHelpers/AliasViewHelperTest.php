<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\ViewHelpers\AliasViewHelper;

/**
 * Testcase for AliasViewHelper
 */
class AliasViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @test
     */
    public function testInitializeArgumentsRegistersExpectedArguments(): void
    {
        $instance = $this->getMock(AliasViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('map', 'array', $this->anything(), true);
        $instance->initializeArguments();
    }

    /**
     * @test
     */
    public function renderAddsSingleValueToTemplateVariableContainerAndRemovesItAfterRendering(): void
    {
        $viewHelper = new AliasViewHelper();
        $viewHelper->addChild(new ObjectAccessorNode('someAlias'));

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setArguments(['map' => ['someAlias' => 'someValue']]);
        $output = $viewHelper->render();
        $this->assertSame('someValue', $output);
    }

    /**
     * @test
     */
    public function renderAddsMultipleValuesToTemplateVariableContainerAndRemovesThemAfterRendering(): void
    {
        $viewHelper = new AliasViewHelper();
        $viewHelper->addChild(new ObjectAccessorNode('someAlias'));
        $viewHelper->addChild(new ObjectAccessorNode('someOtherAlias'));

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setArguments(['map' => ['someAlias' => 'someValue', 'someOtherAlias' => 'someOtherValue']]);
        $output = $viewHelper->render();
        $this->assertSame('someValuesomeOtherValue', $output);
    }

    /**
     * @test
     */
    public function renderDoesNotTouchTemplateVariableContainerAndReturnsChildNodesIfMapIsEmpty(): void
    {
        $viewHelper = new AliasViewHelper();
        $viewHelper->addChild(new TextNode('foo'));

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setArguments(['map' => []]);
        $this->assertEquals('foo', $viewHelper->render());
    }
}
