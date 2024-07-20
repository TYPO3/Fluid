<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser\TemplateProcessor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;

final class NamespaceDetectionTemplateProcessorTest extends AbstractFunctionalTestCase
{
    public static function preProcessSourceExtractsNamespacesDataProvider(): array
    {
        return [
            'does nothing with empty templates' => [
                '',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                ],
                '',
            ],
            'supports expression node style namespaces' => [
                '{namespace x=X\\Y\\ViewHelpers}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                '',
            ],
            'ignores blank expression node style namespaces' => [
                '{namespace z}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'z' => null,
                ],
                '',
            ],
            'ignores unknown namespaces' => [
                '<html xmlns:unknown="http://not.from.here/ns/something" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'unknown' => null,
                ],
                PHP_EOL,
            ],
            'ignores unknown namespaces with https' => [
                '<html xmlns:unknown="https://not.from.here/ns/something" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'unknown' => null,
                ],
                PHP_EOL,
            ],
            'supports xmlns detection, single' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports xmlns detection, leave tag in place' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL . '</html>',
            ],
            'supports xmlns detection, multiple' => [
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:z="http://typo3.org/ns/X/Z/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                    'z' => ['X\\Z\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports expression style namespace detection, camelCase' => [
                '{namespace camelCase=X\\Y\\ViewHelpers}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers'],
                ],
                '',
            ],
            'supports xmlns detection, camelCase' => [
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
        ];
    }

    #[DataProvider('preProcessSourceExtractsNamespacesDataProvider')]
    #[Test]
    public function preProcessSourceExtractsNamespaces(string $templateSource, array $expectedNamespaces, string $expectedSource): void
    {
        $viewHelperResolver = new ViewHelperResolver();
        $renderingContext = new RenderingContext();
        $renderingContext->setViewHelperResolver($viewHelperResolver);
        $subject = new NamespaceDetectionTemplateProcessor();
        $subject->setRenderingContext($renderingContext);
        $result = $subject->preProcessSource($templateSource);
        self::assertSame($expectedSource, $result);
        self::assertSame($expectedNamespaces, $viewHelperResolver->getNamespaces());
    }

    #[Test]
    public function throwsErrorForInvalidFluidNamespace(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1721467847);
        $subject = new NamespaceDetectionTemplateProcessor();
        $subject->setRenderingContext(new RenderingContext());
        $subject->preProcessSource('<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:z="https://typo3.org/ns/X/Z/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>');
    }
}
