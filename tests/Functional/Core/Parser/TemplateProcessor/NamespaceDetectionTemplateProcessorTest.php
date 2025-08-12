<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser\TemplateProcessor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
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
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                ],
                '',
            ],
            'supports expression node style namespaces' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '{namespace x=X\\Y\\ViewHelpers}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                '',
            ],
            'ignores blank expression node style namespaces' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '{namespace z}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'z' => null,
                ],
                '',
            ],
            'ignores unknown namespaces' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:unknown="http://not.from.here/ns/something" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'unknown' => null,
                ],
                PHP_EOL,
            ],
            'ignores unknown namespaces with https' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:unknown="https://not.from.here/ns/something" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'unknown' => null,
                ],
                PHP_EOL,
            ],
            'supports xmlns detection without suffix' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:x="http://typo3.org/ns/X/Y" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y'],
                ],
                PHP_EOL,
            ],
            'supports xmlns detection, single' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports xmlns detection, leave tag in place' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                ],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers">' . PHP_EOL . '</html>',
            ],
            'supports xmlns detection, multiple' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:x="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:z="http://typo3.org/ns/X/Z/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y\\ViewHelpers'],
                    'z' => ['X\\Z\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports expression style namespace detection, camelCase' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '{namespace camelCase=X\\Y\\ViewHelpers}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers'],
                ],
                '',
            ],
            'supports xmlns detection, camelCase' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports multiple expression style calls with same namespace' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '{namespace camelCase=X\\Y\\ViewHelpers}{namespace camelCase=A\\B\\ViewHelpers}',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers', 'A\\B\\ViewHelpers'],
                ],
                '',
            ],
            'supports multiple xmlns calls with same namespace' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers']],
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:camelCase="http://typo3.org/ns/A/B/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['X\\Y\\ViewHelpers', 'A\\B\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports merge with same global namespace' => [
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['Global\\CamelCase\\ViewHelpers'],
                ],
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:camelCase="http://typo3.org/ns/A/B/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['Global\\CamelCase\\ViewHelpers', 'X\\Y\\ViewHelpers', 'A\\B\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'supports merge with ignored global namespace' => [
                ['f' => ['TYPO3Fluid\Fluid\ViewHelpers'], 'x' => null],
                '<html xmlns:x="http://typo3.org/ns/X/Y" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'x' => ['X\\Y'],
                ],
                PHP_EOL,
            ],
            'ignores duplicates during merge with same global namespace' => [
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['A\\B\\ViewHelpers', 'Global\\CamelCase\\ViewHelpers'],
                ],
                '<html xmlns:camelCase="http://typo3.org/ns/X/Y/ViewHelpers" xmlns:camelCase="http://typo3.org/ns/A/B/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'f' => ['TYPO3Fluid\Fluid\ViewHelpers'],
                    'camelCase' => ['A\\B\\ViewHelpers', 'Global\\CamelCase\\ViewHelpers', 'X\\Y\\ViewHelpers'],
                ],
                PHP_EOL,
            ],
            'TYPO3 template with xmlns for f namespace' => [
                [
                    'core' => [
                        'TYPO3\\CMS\\Core\\ViewHelpers',
                    ],
                    'f' => [
                        'TYPO3Fluid\\Fluid\\ViewHelpers',
                        'TYPO3\\CMS\\Fluid\\ViewHelpers',
                    ],
                ],
                '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'core' => [
                        'TYPO3\\CMS\\Core\\ViewHelpers',
                    ],
                    'f' => [
                        'TYPO3Fluid\\Fluid\\ViewHelpers',
                        'TYPO3\\CMS\\Fluid\\ViewHelpers',
                    ],
                ],
                PHP_EOL,
            ],
            'TYP3 template with diverging xmlns for f namespace' => [
                [
                    'core' => [
                        'TYPO3\\CMS\\Core\\ViewHelpers',
                    ],
                    'f' => [
                        'TYPO3Fluid\\Fluid\\ViewHelpers',
                        'TYPO3\\CMS\\Fluid\\ViewHelpers',
                    ],
                ],
                '<html xmlns:f="http://xsd.helhum.io/ns/typo3/cms-fluid/master/ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>',
                [
                    'core' => [
                        'TYPO3\\CMS\\Core\\ViewHelpers',
                    ],
                    'f' => [
                        'TYPO3Fluid\\Fluid\\ViewHelpers',
                        'TYPO3\\CMS\\Fluid\\ViewHelpers',
                        null,
                    ],
                ],
                PHP_EOL,
            ],
        ];
    }

    #[DataProvider('preProcessSourceExtractsNamespacesDataProvider')]
    #[Test]
    public function preProcessSourceExtractsNamespaces(array $initialNamespaces, string $templateSource, array $expectedNamespaces, string $expectedSource): void
    {
        $viewHelperResolver = new ViewHelperResolver();
        $viewHelperResolver->setNamespaces($initialNamespaces);
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

    #[Test]
    #[IgnoreDeprecations]
    public function phpNamespaceInXmlns(): void
    {
        $subject = new NamespaceDetectionTemplateProcessor();
        $renderingContext = new RenderingContext();
        $subject->setRenderingContext($renderingContext);
        $subject->preProcessSource('<html xmlns:x="TYPO3Fluid\\Fluid\\ViewHelpers" data-namespace-typo3-fluid="true">' . PHP_EOL . '</html>');
        self::assertSame(['TYPO3Fluid\\Fluid\\ViewHelpers'], $renderingContext->getViewHelperResolver()->getNamespaces()['x']);
    }
}
