<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for IfViewHelper
 */
class IfViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $thenViewHelper = (new ThenViewHelper())->addChild(new TextNode('yes'));
        $elseViewHelper = (new ElseViewHelper())->addChild(new TextNode('no'));

        $elseViewHelper2 = new ElseViewHelper();
        $elseViewHelper2->onOpen($context)->addChild(new TextNode('matchedcondition'));

        $elseViewHelper3 = (new ElseViewHelper())->addChild(new TextNode('nocondition'));

        return [
            'renders condition based on then and else arguments with condition true' => ['yes', $context, ['condition' => true, 'then' => 'yes', 'else' => 'no']],
            'renders condition based on then and else arguments with condition false' => ['no', $context, ['condition' => false, 'then' => 'yes', 'else' => 'no']],
            'renders content argument if then argument not provided and f:then not used' => [
                'yes',
                $context,
                ['condition' => true],
                [new TextNode('yes')],
            ],
            'renders condition based on f:then and f:else child nodes with condition true' => [
                'yes',
                $context,
                ['condition' => true],
                [$thenViewHelper, $elseViewHelper],
            ],
            'ignores non-condition child nodes as sibling of f:then and f:else' => [
                'yes',
                $context,
                ['condition' => true],
                [$thenViewHelper, new TextNode('notseen'), $elseViewHelper],
            ],
            'renders condition based on f:then and f:else child nodes with condition false' => [
                'no',
                $context,
                ['condition' => false],
                [$thenViewHelper, $elseViewHelper],
            ],
            'renders condition based on f:then and multiple f:else child nodes with else-if conditions with first f:else match' => [
                'matchedcondition',
                $context,
                ['condition' => false],
                [
                    $thenViewHelper,
                    $elseViewHelper2,
                    $elseViewHelper3,
                ],
            ],
            'renders condition based on f:then and multiple f:else child nodes with unmatched else-if and last f:else without condition' => [
                'nocondition',
                $context,
                ['condition' => false],
                [
                    (new ThenViewHelper())->addChild(new TextNode('yes')),
                    $elseViewHelper3,
                    $elseViewHelper2,
                ],
            ],
        ];
    }
}
