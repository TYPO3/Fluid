<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

use stdClass;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class RenderingContextTest extends UnitTestCase
{
    public static function gettersReturnPreviouslySetValuesDataProvider(): array
    {
        return [
            ['controllerName', 'foobar-controllerName'],
            ['controllerAction', 'foobar-controllerAction'],
            ['expressionNodeTypes', ['Foo', 'Bar']],
        ];
    }

    /**
     * @test
     * @dataProvider gettersReturnPreviouslySetValuesDataProvider
     */
    public function gettersReturnPreviouslySetValues(string $property, string|array $expected): void
    {
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst($property);
        $subject->$setter($expected);
        $getter = 'get' . ucfirst($property);
        self::assertSame($expected, $subject->$getter());
    }

    public static function gettersReturnPreviouslySetObjectsDataProvider(): array
    {
        return [
            ['variableProvider', VariableProviderInterface::class],
            ['viewHelperResolver', ViewHelperResolver::class],
            ['viewHelperInvoker', ViewHelperInvoker::class],
            ['templatePaths', TemplatePaths::class],
            ['cache', SimpleFileCache::class],
            ['templateParser', TemplateParser::class],
            ['templateCompiler', TemplateCompiler::class],
            ['viewHelperVariableContainer', ViewHelperVariableContainer::class],
        ];
    }

    /**
     * @test
     * @dataProvider gettersReturnPreviouslySetObjectsDataProvider
     */
    public function gettersReturnPreviouslySetObjects(string $property, string $expected): void
    {
        $expected = $this->createMock($expected);
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst($property);
        $subject->$setter($expected);
        $getter = 'get' . ucfirst($property);
        self::assertSame($expected, $subject->$getter());
    }

    /**
     * @test
     */
    public function getTemplateProcessorsReturnsPreviouslySetTemplateProcessor(): void
    {
        $processors = [$this->createMock(TemplateProcessorInterface::class), $this->createMock(TemplateProcessorInterface::class)];
        $subject = new RenderingContext();
        $subject->setTemplateProcessors($processors);
        self::assertSame($processors, $subject->getTemplateProcessors());
    }

    /**
     * @test
     */
    public function isCacheEnabledReturnsFalse(): void
    {
        $subject = new RenderingContext();
        self::assertFalse($subject->isCacheEnabled());
    }

    /**
     * @test
     */
    public function isCacheEnabledReturnsTrueIfCacheIsEnabled(): void
    {
        $subject = new RenderingContext();
        $subject->setCache($this->createMock(FluidCacheInterface::class));
        self::assertTrue($subject->isCacheEnabled());
    }

    /**
     * @test
     */
    public function withAndGetAttribute(): void
    {
        $object = new stdClass();
        $object->test = 'value';

        $subject = new RenderingContext();
        $clonedSubject = $subject->withAttribute('test', $object);
        self::assertNull($subject->getAttribute('test'));
        self::assertEquals($object, $clonedSubject->getAttribute('test'));
    }
}
