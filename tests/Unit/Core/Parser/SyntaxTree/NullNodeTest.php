<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NullNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class NullNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function renderReturnsNullGivenInConstructor(): void
    {
        $renderingContext = new RenderingContext();
        $node = new NullNode('null');
        self::assertEquals($node->evaluate($renderingContext), null, 'The rendered value of a null node does not match the string given in the constructor.');
    }
}
