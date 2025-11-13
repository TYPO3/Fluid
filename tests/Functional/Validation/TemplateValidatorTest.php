<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\Validation\TemplateValidator;

final class TemplateValidatorTest extends AbstractFunctionalTestCase
{
    public static function validatorFindsParseErrorsDataProvider(): array
    {
        $fixturePath = __DIR__ . '/../Fixtures/Validation/';
        return [
            [$fixturePath . 'NotProperlyNested.fluid.html', 'Not all tags were closed!', 1238169398],
            [$fixturePath . 'InvalidNamespace.fluid.html', 'Unknown Namespace: foo', 0],
            [$fixturePath . 'RequiredArgumentMissing.fluid.html', 'Required argument "each" was not supplied.', 1237823699],
            [$fixturePath . 'RedefinedComponentArgument.fluid.html', 'Template argument "foo" has been defined multiple times.', 1744908509],
        ];
    }

    #[Test]
    #[DataProvider('validatorFindsParseErrorsDataProvider')]
    public function validatorFindsParseErrors(string $path, string $expectedMessagePart, int $expectedExceptionCode): void
    {
        $subject = new TemplateValidator();
        $results = $subject->validateTemplateFiles([$path]);
        self::assertSame($expectedExceptionCode, $results[$path]->errors[0]->getCode());
        self::assertStringContainsString($expectedMessagePart, $results[$path]->errors[0]->getMessage());
        self::assertSame($path, $results[$path]->path);
        self::assertNull($results[$path]->parsedTemplate);
        self::assertFalse($results[$path]->canBeCompiled());
    }

    #[Test]
    public function validatorFindsDeprecations(): void
    {
        $path = __DIR__ . '/../Fixtures/Validation/DeprecatedViewHelper.fluid.html';
        $subject = new TemplateValidator();
        $results = $subject->validateTemplateFiles([$path]);
        self::assertSame('ViewHelper is deprecated.', $results[$path]->deprecations[0]->message);
        self::assertSame($path, $results[$path]->path);
        self::assertInstanceOf(ParsingState::class, $results[$path]->parsedTemplate);
        self::assertTrue($results[$path]->canBeCompiled());
    }

    #[Test]
    public function validatorUsesSuppliedRenderingContext(): void
    {
        $path = __DIR__ . '/../Fixtures/Validation/GlobalNamespace.fluid.html';
        $renderingContext = new RenderingContext();
        $renderingContext->getViewHelperResolver()->addNamespace('test', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers');
        $subject = new TemplateValidator();
        $results = $subject->validateTemplateFiles([$path], $renderingContext);
        self::assertSame([], $results[$path]->errors);
        self::assertSame($path, $results[$path]->path);
        self::assertInstanceOf(ParsingState::class, $results[$path]->parsedTemplate);
        self::assertTrue($results[$path]->canBeCompiled());
    }
}
