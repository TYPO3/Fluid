<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\ParserRuntimeOnly;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\TagGenerating;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\TagGeneratingViewHelperFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class TagGeneratingTest
 */
class TagGeneratingTest extends UnitTestCase
{
    /**
     * @param array $arguments
     * @param string $expected
     * @test
     * @dataProvider getTestValues
     */
    public function testTagGenerator(array $arguments, string $expected)
    {
        $mock = new TagGeneratingViewHelperFixture();
        $this->assertEquals($expected, $mock::renderStatic($arguments, function() { return ''; }, new RenderingContextFixture()));
    }

    /**
     * @param array $arguments
     * @test
     * @dataProvider getTestValues
     */
    public function testHandleAdditionalArguments(array $arguments)
    {
        $mock = $this->getMockBuilder(TagGenerating::class)->getMockForTrait();
        $before = clone $mock;
        $mock->handleAdditionalArguments($arguments);
        $this->assertEquals($before, $mock);
    }

    /**
     * @param array $arguments
     * @test
     * @dataProvider getTestValues
     */
    public function testValidateAdditionalArguments(array $arguments)
    {
        $mock = $this->getMockBuilder(TagGenerating::class)->getMockForTrait();
        $before = clone $mock;
        $mock->validateAdditionalArguments($arguments);
        $this->assertEquals($before, $mock);
    }

    /**
     * @return array
     */
    public function getTestValues(): array
    {
        return [
            [['test' => 'something'], '<div />'],
            [['foobar' => ''], '<div foobar="" />'],
            [['data-something' => 'foo'], '<div data-something="foo" />'],
            [['data' => ['something' => 'foo', 'second' => '2']], '<div data-something="foo" data-second="2" />'],
        ];
    }
}
