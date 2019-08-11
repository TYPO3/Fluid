<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\SequencingException;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class SequencingExceptionTest
 */
class SequencingExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getExcerptReturnsExcerpt(): void
    {
        $subject = new SequencingException('foo');
        $subject->setExcerpt('an excerpt');
        $this->assertSame('an excerpt', $subject->getExcerpt());
    }

    /**
     * @test
     */
    public function getByteReturnsByteValue(): void
    {
        $subject = new SequencingException('foo');
        $subject->setByte(123);
        $this->assertSame(123, $subject->getByte());
    }
}
