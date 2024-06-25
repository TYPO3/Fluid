<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits\Fixtures\ParserRuntimeOnlyFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * @deprecated remove together with ParserRuntimeOnly.
 */
final class ParserRuntimeOnlyTest extends UnitTestCase
{
    #[Test]
    public function renderReturnsNull(): void
    {
        self::assertNull((new ParserRuntimeOnlyFixture())->render());
    }

    #[Test]
    public function compileReturnsEmptyString(): void
    {
        $initializationPhpCode = '';
        $subject = new ParserRuntimeOnlyFixture();
        $result = $subject->compile('', '', $initializationPhpCode, $this->createMock(ViewHelperNode::class), new TemplateCompiler());
        self::assertSame('', $result);
    }
}
