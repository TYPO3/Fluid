<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Compiler;

use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class FailedCompilingStateTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getFailureReasonReturnsPreviouslySetFailureReason(): void
    {
        $subject = new FailedCompilingState();
        $subject->setFailureReason('failed');
        self::assertSame('failed', $subject->getFailureReason());
    }

    /**
     * @test
     */
    public function getMitigationsReturnsPreviouslySetMitigation(): void
    {
        $subject = new FailedCompilingState();
        $subject->setMitigations(['m1', 'm2']);
        self::assertSame(['m1', 'm2'], $subject->getMitigations());
    }

    /**
     * @test
     */
    public function addMitigationAddsAnotherMitigation(): void
    {
        $subject = new FailedCompilingState();
        $subject->setMitigations(['m1']);
        $subject->addMitigation('m2');
        self::assertSame(['m1', 'm2'], $subject->getMitigations());
    }
}
