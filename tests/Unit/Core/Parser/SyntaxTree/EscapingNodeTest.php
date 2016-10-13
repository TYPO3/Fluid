<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for EscapingNode
 */
class EscapingNodeTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testEscapesNodeInConstructor()
    {
        $string = '<strong>escape me</strong>';
        $childNode = new TextNode($string);
        $node = new EscapingNode($childNode);
        $renderingContext = new RenderingContextFixture();
        $this->assertEquals($node->evaluate($renderingContext), htmlspecialchars($string, ENT_QUOTES));
    }

    /**
     * @test
     */
    public function testEscapesNodeOverriddenWithAddChildNode()
    {
        $string1 = '<strong>escape me</strong>';
        $string2 = '<strong>no, escape me!</strong>';
        $node = new EscapingNode(new TextNode($string1));
        $node->addChildNode(new TextNode($string2));
        $renderingContext = new RenderingContextFixture();
        $this->assertEquals($node->evaluate($renderingContext), htmlspecialchars($string2, ENT_QUOTES));
    }
}
