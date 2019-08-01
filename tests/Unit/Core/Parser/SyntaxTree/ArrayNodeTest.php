<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for ArrayNode
 */
class ArrayNodeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function flattenReturnsSelf(): void
    {
        $subject = new ArrayNode();
        $this->assertSame($subject, $subject->flatten());
    }

    /**
     * @test
     */
    public function flattenReturnsSelfWithExtractTrue(): void
    {
        $subject = new ArrayNode();
        $this->assertSame($subject, $subject->flatten(true));
    }

    /**
     * @test
     */
    public function arrayAccessGetWorks(): void
    {
        $subject = new ArrayNode(['foo' => 'bar']);
        $this->assertSame('bar', $subject['foo']);
    }

    /**
     * @test
     */
    public function arrayAccessSetWorks(): void
    {
        $subject = new ArrayNode();
        $subject['foo'] = 'bar';
        $this->assertSame('bar', $subject['foo']);
    }

    /**
     * @test
     */
    public function arrayAccessUnsetWorks(): void
    {
        $subject = new ArrayNode(['foo' => 'bar']);
        unset($subject['foo']);
        $this->assertSame(null, $subject['foo']);
    }

    /**
     * @test
     */
    public function arrayAccessIssetWorks(): void
    {
        $subject = new ArrayNode(['foo' => 'bar']);
        $this->assertSame(true, isset($subject['foo']));
    }
}
