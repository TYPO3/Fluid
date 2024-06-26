<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EscapingNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

final class EscapingNodeTest extends TestCase
{
    #[Test]
    public function testEscapesNodeInConstructor(): void
    {
        $string = '<strong>escape me</strong>';
        $childNode = new TextNode($string);
        $node = new EscapingNode($childNode);
        $renderingContext = new RenderingContext();
        self::assertEquals($node->evaluate($renderingContext), htmlspecialchars($string, ENT_QUOTES));
    }

    #[Test]
    public function testEscapesNodeOverriddenWithAddChildNode(): void
    {
        $string1 = '<strong>escape me</strong>';
        $string2 = '<strong>no, escape me!</strong>';
        $node = new EscapingNode(new TextNode($string1));
        $node->addChildNode(new TextNode($string2));
        $renderingContext = new RenderingContext();
        self::assertEquals($node->evaluate($renderingContext), htmlspecialchars($string2, ENT_QUOTES));
    }
}
