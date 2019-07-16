<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper;

/**
 * Test for \TYPO3Fluid\Fluid\ViewHelpers\Format\HtmlspecialcharsViewHelper
 */
class HtmlspecialcharsViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var HtmlspecialcharsViewHelper|MockObject
     */
    protected $viewHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getMock(HtmlspecialcharsViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     */
    public function viewHelperInitializesArguments(): void
    {
        $this->viewHelper->initializeArguments();
        $this->assertAttributeNotEmpty('argumentDefinitions', $this->viewHelper);
    }

    /**
     * @test
     */
    public function viewHelperDeactivatesEscapingInterceptor(): void
    {
        $this->assertFalse($this->viewHelper->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderUsesValueAsSourceIfSpecified(): void
    {
        $this->viewHelper->expects($this->never())->method('renderChildren');
        $this->viewHelper->setArguments(
            ['value' => 'Some string', 'keepQuotes' => false, 'encoding' => 'UTF-8', 'doubleEncode' => false]
        );
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('Some string', $actualResult);
    }

    /**
     * test
     */
    public function renderUsesChildNodesAsSourceIfSpecified(): void
    {
        $this->viewHelper->expects($this->atLeastOnce())->method('renderChildren')->will($this->returnValue('Some string'));
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertEquals('Some string', $actualResult);
    }

    public function dataProvider(): array
    {
        return [
            // render does not modify string without special characters
            [
                'value' => 'This is a sample text without special characters.',
                'options' => [],
                'expectedResult' => 'This is a sample text without special characters.'
            ],
            // render decodes simple string
            [
                'value' => 'Some special characters: &©"\'',
                'options' => [],
                'expectedResult' => 'Some special characters: &amp;©&quot;&#039;'
            ],
            // render respects "keepQuotes" argument
            [
                'value' => 'Some special characters: &©"',
                'options' => [
                    'keepQuotes' => true,
                ],
                'expectedResult' => 'Some special characters: &amp;©"'
            ],
            // render respects "encoding" argument
            [
                'value' => utf8_decode('Some special characters: &"\''),
                'options' => [
                    'encoding' => 'ISO-8859-1',
                ],
                'expectedResult' => 'Some special characters: &amp;&quot;&#039;'
            ],
            // render converts already converted entities by default
            [
                'value' => 'already &quot;encoded&quot;',
                'options' => [],
                'expectedResult' => 'already &amp;quot;encoded&amp;quot;'
            ],
            // render does not convert already converted entities if "doubleEncode" is FALSE
            [
                'value' => 'already &quot;encoded&quot;',
                'options' => [
                    'doubleEncode' => false,
                ],
                'expectedResult' => 'already &quot;encoded&quot;'
            ],
            // render returns unmodified source if it is a float
            [
                'value' => 123.45,
                'options' => [],
                'expectedResult' => 123.45
            ],
            // render returns unmodified source if it is an integer
            [
                'value' => 12345,
                'options' => [],
                'expectedResult' => 12345
            ],
            // render returns unmodified source if it is a boolean
            [
                'value' => true,
                'options' => [],
                'expectedResult' => true
            ],
        ];
    }

    /**
     * test
     *
     * @dataProvider dataProvider
     */
    public function renderTests($value, array $options, $expectedResult): void
    {
        $options['value'] = $value;
        $this->viewHelper->setArguments($options);
        $this->assertSame($expectedResult, $this->viewHelper->initializeArgumentsAndRender());
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function renderTestsWithRenderChildrenFallback($value, array $options, $expectedResult): void
    {
        $this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue($value));
        $options['value'] = null;
        $options['keepQuotes'] = (boolean) (isset($options['keepQuotes']) && $options['keepQuotes'] ? $options['keepQuotes'] : false);
        $options['encoding'] = 'UTF-8';
        $options['doubleEncode'] = (boolean) (isset($options['doubleEncode']) ? $options['doubleEncode'] : true);
        $this->viewHelper->setArguments($options);
        $this->assertSame($expectedResult, $this->viewHelper->initializeArgumentsAndRender());
    }

    /**
     * __test
     */
    public function renderConvertsObjectsToStrings(): void
    {
        $user = new UserWithToString('Xaver <b>Cross-Site</b>');
        $expectedResult = 'Xaver &lt;b&gt;Cross-Site&lt;/b&gt;';
        $this->viewHelper->setArguments(['value' => $user]);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * __test
     */
    public function renderDoesNotModifySourceIfItIsAnObjectThatCantBeConvertedToAString(): void
    {
        $user = new UserWithoutToString('Xaver <b>Cross-Site</b>');
        $this->viewHelper->setArguments(['value' => $user]);
        $actualResult = $this->viewHelper->initializeArgumentsAndRender();
        $this->assertSame($user, $actualResult);
    }
}
