<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Source;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class SourceTest
 */
class SourceTest extends UnitTestCase
{
    public function testSourceCanBeCreatedAndCountsBytesAndLength(): void
    {
        $string = 'I am a source';
        $source = new Source($string);
        $this->assertSame($string, $source->source);
        $this->assertSame(strlen($string), $source->length);
        $this->assertSame(unpack('C*', $string), $source->bytes);
    }

    public function testCastToStringReturnsSource(): void
    {
        $string = 'I am a source';
        $source = new Source($string);
        $this->assertSame($string, (string) $source);
    }
}
