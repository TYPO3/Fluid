<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\SyntaxTree;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NumericNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for NumericNode
 */
class NumericNodeTest extends UnitTestCase
{

    /**
     * @var RenderingContext
     */
    protected $renderingContext;

    public function setUp(): void
    {
        $this->renderingContext = new RenderingContextFixture();
    }

    /**
     * @test
     */
    public function renderReturnsProperIntegerGivenInConstructor(): void
    {
        $string = '1';
        $node = new NumericNode($string);
        $this->assertEquals($node->execute($this->renderingContext), 1, 'The rendered value of a numeric node does not match the string given in the constructor.');
    }

    /**
     * @test
     */
    public function renderReturnsProperFloatGivenInConstructor(): void
    {
        $string = '1.1';
        $node = new NumericNode($string);
        $this->assertEquals($node->execute($this->renderingContext), 1.1, 'The rendered value of a numeric node does not match the string given in the constructor.');
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfNoNumericGiven(): void
    {
        $this->expectException(Exception::class);
        new NumericNode('foo');
    }

    /**
     * @test
     */
    public function addChildThrowsException(): void
    {
        $this->expectException(Exception::class);

        $node = new NumericNode('1');
        $node->addChild(clone $node);
    }
}
