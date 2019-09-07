<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;

/**
 * Testcase for ExtendViewHelper
 */
class ExtendViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $atom = new EntryNode();
        $atom->addChild(new TextNode('foobar'));
        $context = new RenderingContextFixture();
        $resolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['resolveAtom'])->setConstructorArgs([$context])->getMock();
        $resolver->expects($this->atLeastOnce())->method('resolveAtom')->with('foo', 'bar')->willReturn($atom);
        $context->setViewHelperResolver($resolver);
        return [
            'returns null on execution without child nodes' => [null, $context, ['atom' => 'foo:bar']],
        ];
    }
}
