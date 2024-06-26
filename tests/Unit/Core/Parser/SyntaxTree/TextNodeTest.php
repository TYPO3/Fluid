<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

final class TextNodeTest extends TestCase
{
    #[Test]
    public function renderReturnsSameStringAsGivenInConstructor(): void
    {
        $string = 'I can work quite effectively in a train!';
        $node = new TextNode($string);
        $renderingContext = new RenderingContext();
        self::assertEquals($node->evaluate($renderingContext), $string, 'The rendered string of a text node is not the same as the string given in the constructor.');
    }
}
