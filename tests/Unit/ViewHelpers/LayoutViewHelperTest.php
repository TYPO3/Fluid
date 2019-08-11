<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Testcase for LayoutViewHelper
 */
class LayoutViewHelperTest extends ViewHelperBaseTestCase
{
    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['getLayoutPathAndFilename'])->disableOriginalConstructor()->getMock();
        $paths->expects($this->once())->method('getLayoutPathAndFilename')->willReturn('test');
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['parseFile'])->disableOriginalConstructor()->getMock();
        $parser->expects($this->once())->method('parseFile')->willReturn(new EntryNode());
        $context->setTemplateParser($parser);
        $context->setTemplatePaths($paths);
        return [
            'returns child nodes on execution' => ['child', $context, ['name' => 'layout'], [new TextNode('child')]],
        ];
    }
}
