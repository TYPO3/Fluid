<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingChildrenException;

/**
 * @deprecated Remove together with StopCompilingChildrenException
 */
final class StopCompilingChildrenExceptionTest extends TestCase
{
    #[Test]
    public function setAndGetReplacementString(): void
    {
        $subject = new StopCompilingChildrenException();
        $subject->setReplacementString('test replacement string');
        self::assertEquals('test replacement string', $subject->getReplacementString());
    }
}
