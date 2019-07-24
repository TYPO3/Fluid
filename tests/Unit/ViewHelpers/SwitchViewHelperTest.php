<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CaseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\DefaultCaseViewHelper;

/**
 * Testcase for SwitchViewHelper
 */
class SwitchViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'returns null for empty switch' => [null, $context, ['expression' => 'foo']],
            'returns matching case node content with single case node' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [(new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']))->addChild(new TextNode('foo'))],
            ],
            'returns matching first case node content with multiple case nodes' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']))->addChild(new TextNode('foo')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'bar']))->addChild(new TextNode('bar')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'baz']))->addChild(new TextNode('baz')),
                ],
            ],
            'returns matching last case node content with multiple case nodes' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'bar']))->addChild(new TextNode('bar')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'baz']))->addChild(new TextNode('baz')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']))->addChild(new TextNode('foo')),
                ],
            ],
            'returns null for no matching case nodes without default case node' => [
                null,
                $context,
                ['expression' => 'notfound'],
                [
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'bar']))->addChild(new TextNode('bar')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'baz']))->addChild(new TextNode('baz')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'foo']))->addChild(new TextNode('foo')),
                ],
            ],
            'returns default case node content for no matching case nodes' => [
                'foo',
                $context,
                ['expression' => 'notfound'],
                [
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'bar']))->addChild(new TextNode('bar')),
                    (new CaseViewHelper())->onOpen($context, (new ArgumentCollection())->assignAll(['value' => 'baz']))->addChild(new TextNode('baz')),
                    (new DefaultCaseViewHelper())->addChild(new TextNode('foo')),
                ],
            ],
        ];
    }
}
