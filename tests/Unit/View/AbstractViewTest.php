<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Tests\Unit\View\Fixtures\AbstractViewTestFixture;

final class AbstractViewTest extends TestCase
{
    #[Test]
    public function renderReturnsEmptyString(): void
    {
        $subject = new AbstractViewTestFixture();
        self::assertSame('', $subject->render());
    }

    #[Test]
    public function testAssignsVariableAndReturnsSelf(): void
    {
        $subject = new AbstractViewTestFixture();
        $subject->assign('test', 'foobar');
        self::assertSame(['test' => 'foobar'], $subject->variables);
    }

    #[Test]
    public function testAssignsMultipleVariablesAndReturnsSelf(): void
    {
        $subject = new AbstractViewTestFixture();
        $subject->assignMultiple(['test' => 'foobar', 'baz' => 'barfoo']);
        self::assertSame(['test' => 'foobar', 'baz' => 'barfoo'], $subject->variables);
    }
}
