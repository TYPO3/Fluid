<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

final class AbstractTagBasedViewHelperTest extends TestCase
{
    #[Test]
    public function renderCallsRenderOnTagBuilder(): void
    {
        $tagBuilder = $this->createMock(TagBuilder::class);
        $tagBuilder->expects(self::once())->method('render')->willReturn('foobar');
        $subject = $this->getMockBuilder(AbstractTagBasedViewHelper::class)->onlyMethods([])->getMock();
        $subject->setTagBuilder($tagBuilder);
        self::assertEquals('foobar', $subject->render());
    }
}
