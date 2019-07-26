<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
        $caseFoo = (new CaseViewHelper())->onOpen($context)->addChild(new TextNode('foo'));
        $caseFoo->getArguments()->assignAll(['value' => 'foo']);
        $caseBar = (new CaseViewHelper())->onOpen($context)->addChild(new TextNode('bar'));
        $caseBar->getArguments()->assignAll(['value' => 'bar']);
        $caseBaz = (new CaseViewHelper())->onOpen($context)->addChild(new TextNode('baz'));
        $caseBaz->getArguments()->assignAll(['value' => 'baz']);
        return [
            'null for empty switch' => [
                null,
                $context,
                ['expression' => 'foo']
            ],
            'matching case node content with single case node' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [$caseFoo],
            ],
            'matching first case node content with multiple case nodes' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [$caseFoo, $caseBar, $caseBaz],
            ],
            'matching last case node content with multiple case nodes' => [
                'foo',
                $context,
                ['expression' => 'foo'],
                [$caseBar, $caseBaz, $caseFoo],
            ],
            'null for no matching case nodes without default case node' => [
                null,
                $context,
                ['expression' => 'notfound'],
                [$caseBar, $caseBaz, $caseFoo],
            ],
            'default case node content for no matching case nodes' => [
                'foo',
                $context,
                ['expression' => 'notfound'],
                [$caseBar, $caseBaz, (new DefaultCaseViewHelper())->addChild(new TextNode('foo'))],
            ],
        ];
    }
}
