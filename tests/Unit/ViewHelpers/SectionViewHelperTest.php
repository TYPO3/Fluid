<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\SectionNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\ViewHelpers\SectionViewHelper;

/**
 * Testcase for SectionViewHelper
 */
class SectionViewHelperTest extends UnitTestCase
{

    /**
     * @test
     */
    public function onOpenAndCloseGeneratesSectionNode(): void
    {
        $section = new SectionViewHelper();
        $context = new RenderingContextFixture();
        $newNode = $section->onOpen($context, (new ArgumentCollection($section->prepareArguments()))->assignAll(['name' => 'test']))->onClose($context);
        $this->assertInstanceOf(SectionNode::class, $newNode);
    }
}
