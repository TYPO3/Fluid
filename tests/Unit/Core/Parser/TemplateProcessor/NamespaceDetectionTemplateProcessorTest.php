<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser\TemplateProcessor;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for NamespaceDetectionTemplateProcessor
 */
class NamespaceDetectionTemplateProcessorTest extends UnitTestCase
{

    /**
     * @param string $templateSource
     * @param array $expectedNamespaces
     * @param string $expectedSource
     * @dataProvider getTestValues
     */
    public function testExtractsExpectedNamespaces($templateSource, array $expectedNamespaces, $expectedSource)
    {
        $renderingContext = new RenderingContextFixture();
        $viewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['addNamespace'])->getMock();
        foreach ($expectedNamespaces as $index => $expectedNamespace) {
            list ($expectedNamespaceAlias, $expectedNamespacePhp) = $expectedNamespace;
            $viewHelperResolver->expects($this->at($index))
                ->method('addNamespace')
                ->with($expectedNamespaceAlias, $expectedNamespacePhp);
        }
        $renderingContext->setViewHelperResolver($viewHelperResolver);
        $subject = new NamespaceDetectionTemplateProcessor();
        $subject->setRenderingContext($renderingContext);
        $result = $subject->preProcessSource($templateSource);
        $this->assertSame($expectedSource, $result);
    }

    /**
     * @return array
     */
    public function getTestValues()
    {
        return [
            'does nothing with empty templates' => [
                '',
                [],
                ''
            ],
            'supports expression node style namespaces' => [
                '{namespace x=X\\Y\\ViewHelpers}',
                [
                    ['x', 'X\\Y\\ViewHelpers']
                ],
                ''
            ],
            'ignores blank expression node style namespaces' => [
                '{namespace z}',
                [
                    ['z', null]
                ],
                ''
            ],
            'ignores unknown namespaces' => [
                '<html xmlns:unknown="http://not.from.here/ns/something" data-namespace-typo3-fluid="true">' . PHP_EOL. '</html>',
                [
                    ['unknown', null]
                ],
                PHP_EOL
            ],
            'supports xmlns detection, single' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL. '</html>',
                [
                    ['x', 'X\\Y\\ViewHelpers']
                ],
                PHP_EOL
            ],
            'supports xmlns detection, leave tag in place' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL. '</html>',
                [
                    ['x', 'X\\Y\\ViewHelpers']
                ],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL . '</html>'
            ],
            'supports xmlns detection, multiple' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:z="http://typo3.org/ns/X/Z/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL. '</html>',
                [
                    ['x', 'X\\Y\\ViewHelpers'],
                    ['z', 'X\\Z\\ViewHelpers']
                ],
                PHP_EOL
            ],
            'supports expression style namespace detection, camelCase' => [
                '{namespace camelCase=X\\Y\\ViewHelpers}',
                [
                    ['camelCase', 'X\\Y\\ViewHelpers']
                ],
                ''
            ],
            'supports xmlns detection, camelCase' => [
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL. '</html>',
                [
                    ['camelCase', 'X\\Y\\ViewHelpers']
                ],
                PHP_EOL
            ],
        ];
    }

}
