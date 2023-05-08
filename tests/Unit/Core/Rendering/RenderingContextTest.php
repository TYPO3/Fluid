<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplatePaths;
use TYPO3Fluid\Fluid\View\TemplateView;

class RenderingContextTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getTemplateProcessorsReturnsExpectedResult(): void
    {
        $get = [$this->createMock(TemplateProcessorInterface::class), $this->createMock(TemplateProcessorInterface::class)];
        $view = new TemplateView();
        $subject = $this->getAccessibleMock(RenderingContext::class, [], [$view]);
        $subject->_set('templateProcessors', $get);
        $getter = 'get' . ucfirst('templateProcessors');
        self::assertSame($get, $subject->$getter());
    }

    /**
     * @test
     */
    public function setTemplateProcessorsSetsAttribute(): void
    {
        $set = [$this->createMock(TemplateProcessorInterface::class), $this->createMock(TemplateProcessorInterface::class)];
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst('templateProcessors');
        $subject->$setter($set);
        self::assertAttributeSame($set, 'templateProcessors', $subject);
    }

    public static function simpleTypeDataProvider(): array
    {
        return [
            ['variableProvider', new StandardVariableProvider(['foo' => 'bar'])],
            ['controllerName', 'foobar-controllerName'],
            ['controllerAction', 'foobar-controllerAction'],
            ['expressionNodeTypes', ['Foo', 'Bar']],
        ];
    }

    /**
     * @test
     * @param mixed $expected
     * @dataProvider simpleTypeDataProvider
     */
    public function getSimpleTypesReturnsExpectedResult(string $property, $expected): void
    {
        $view = new TemplateView();
        $subject = $this->getAccessibleMock(RenderingContext::class, [], [$view]);
        $subject->_set($property, $expected);
        $getter = 'get' . ucfirst($property);
        self::assertSame($expected, $subject->$getter());
    }

    /**
     * @test
     * @param mixed $expected
     * @dataProvider simpleTypeDataProvider
     */
    public function setSimpleTypeSetsAttribute(string $property, $expected): void
    {
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst($property);
        $subject->$setter($expected);
        self::assertAttributeSame($expected, $property, $subject);
    }

    public static function objectTypeDataProvider(): array
    {
        return [
            ['viewHelperResolver', ViewHelperResolver::class],
            ['viewHelperInvoker', ViewHelperInvoker::class],
            ['templatePaths', TemplatePaths::class],
            ['cache', SimpleFileCache::class],
            ['templateParser', TemplateParser::class],
            ['templateCompiler', TemplateCompiler::class],
        ];
    }

    /**
     * @test
     * @dataProvider objectTypeDataProvider
     */
    public function getObjectTypesReturnsExpectedResult(string $property, string $expected): void
    {
        $expected = $this->createMock($expected);
        $view = new TemplateView();
        $subject = $this->getAccessibleMock(RenderingContext::class, [], [$view]);
        $subject->_set($property, $expected);
        $getter = 'get' . ucfirst($property);
        self::assertSame($expected, $subject->$getter());
    }

    /**
     * @test
     * @dataProvider objectTypeDataProvider
     */
    public function setObjectTypeSetsAttribute(string $property, string $expected): void
    {
        $expected = $this->createMock($expected);
        $subject = new RenderingContext();
        $setter = 'set' . ucfirst($property);
        $subject->$setter($expected);
        self::assertAttributeSame($expected, $property, $subject);
    }

    /**
     * @test
     */
    public function templateVariableContainerCanBeReadCorrectly(): void
    {
        $templateVariableContainer = $this->createMock(VariableProviderInterface::class);
        $renderingContextFixture = new RenderingContextFixture();
        $renderingContextFixture->setVariableProvider($templateVariableContainer);
        self::assertSame($renderingContextFixture->getVariableProvider(), $templateVariableContainer, 'Template Variable Container could not be read out again.');
    }

    /**
     * @test
     */
    public function viewHelperVariableContainerCanBeReadCorrectly(): void
    {
        $viewHelperVariableContainer = $this->createMock(ViewHelperVariableContainer::class);
        $renderingContextFixture = new RenderingContextFixture();
        $renderingContextFixture->setViewHelperVariableContainer($viewHelperVariableContainer);
        self::assertSame($viewHelperVariableContainer, $renderingContextFixture->getViewHelperVariableContainer());
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
}
