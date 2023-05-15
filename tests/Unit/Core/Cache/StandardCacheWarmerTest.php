<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Cache;

use TYPO3Fluid\Fluid\Core\Cache\StandardCacheWarmer;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class StandardCacheWarmerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function testDetectControllerNamesInTemplateRootPaths(): void
    {
        $subject = new StandardCacheWarmer();
        $directory = realpath(__DIR__ . '/../../../../examples/Resources/Private/Templates/');
        $method = new \ReflectionMethod($subject, 'detectControllerNamesInTemplateRootPaths');
        $generator = $method->invoke($subject, [$directory]);
        foreach ($generator as $resolvedControllerName) {
            self::assertNotEmpty($resolvedControllerName, 'Generator yielded an empty controller name!');
        }
    }

    public static function warmupSingleFileHandlesExceptionDataProvider(): array
    {
        return [
            [new StopCompilingException('StopCompiling exception')],
            [new ExpressionException('Expression exception')],
            [new Exception('Parser exception')],
            [new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception('ViewHelper exception')],
            [new \TYPO3Fluid\Fluid\Core\Exception('Fluid core exception')],
            [new \TYPO3Fluid\Fluid\View\Exception('Fluid view exception')],
            [new \RuntimeException('General runtime exception')]
        ];
    }

    /**
     * @dataProvider warmupSingleFileHandlesExceptionDataProvider
     * @test
     */
    public function warmupSingleFileHandlesException(\RuntimeException $error): void
    {
        $templateParserMock = $this->createMock(TemplateParser::class);
        $templateParserMock->expects(self::once())->method('getOrParseAndStoreTemplate')->willThrowException($error);
        $renderingContextMock = $this->createMock(RenderingContextInterface::class);
        $renderingContextMock->expects(self::once())->method('getVariableProvider')->willReturn(new StandardVariableProvider());
        $renderingContextMock->expects(self::once())->method('getTemplateParser')->willReturn($templateParserMock);
        $subject = new StandardCacheWarmer();
        $method = new \ReflectionMethod($subject, 'warmSingleFile');
        $result = $method->invoke($subject, '/some/file', 'some_file', $renderingContextMock);
        self::assertNotEmpty($result->getFailureReason());
        self::assertNotEmpty($result->getMitigations());
    }

    /**
     * @test
     */
    public function testCreateClosureCreatesFileReadingClosure(): void
    {
        $subject = new StandardCacheWarmer();
        $method = new \ReflectionMethod($subject, 'createClosure');
        $closure = $method->invoke($subject, __FILE__);
        self::assertNotEmpty($closure(new TemplateParser(), new TemplatePaths()));
    }
}
