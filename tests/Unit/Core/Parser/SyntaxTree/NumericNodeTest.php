<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class NumericNodeTest extends UnitTestCase
{
    #[Test]
    public function renderReturnsProperIntegerGivenInConstructor(): void
    {
        $renderingContext = new RenderingContext();
        $string = '1';
        $node = new NumericNode($string);
        self::assertEquals($node->evaluate($renderingContext), 1, 'The rendered value of a numeric node does not match the string given in the constructor.');
    }

    #[Test]
    public function renderReturnsProperFloatGivenInConstructor(): void
    {
        $renderingContext = new RenderingContext();
        $string = '1.1';
        $node = new NumericNode($string);
        self::assertEquals($node->evaluate($renderingContext), 1.1, 'The rendered value of a numeric node does not match the string given in the constructor.');
    }

    #[Test]
    public function constructorThrowsExceptionIfNoNumericGiven(): void
    {
        $this->expectException(Exception::class);
        new NumericNode('foo');
    }

    #[Test]
    public function addChildNodeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $node = new NumericNode('1');
        $node->addChildNode(clone $node);
    }
}
