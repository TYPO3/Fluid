<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TemplateParser.
 *
 * This is to at least half a system test, as it compares rendered results to
 * expectations, and does not strictly check the parsing...
 */
class TemplateParserTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getOrParseAndStoreTemplateCallsParseTemplateSourceWithDisabledRuntimeCache(): void
    {
        $context = new RenderingContextFixture();
        $context->getParserConfiguration()->setFeatureState(Configuration::FEATURE_RUNTIME_CACHE, false);
        $parser = $this->getMockBuilder(TemplateParser::class)->setMethods(['parseTemplateSource'])->setConstructorArgs([$context])->getMock();
        $parser->expects($this->once())->method('parseTemplateSource');
        $parser->getOrParseAndStoreTemplate('foo', function(RenderingContextInterface $context) {});
    }

    /**
     * @test
     */
    public function parsingSameFileTwiceReturnsSameInstance(): void
    {
        $context = new RenderingContextFixture();
        $subject = new TemplateParser($context);
        $string = __DIR__ . '/../../../Fixtures/Atoms/testAtom.html';
        $instance1 = $subject->parseFile($string);
        $instance2 = $subject->parseFile($string);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     */
    public function fileCanBeParsedWithRuntimeCacheDisabled(): void
    {
        $context = new RenderingContextFixture();
        $subject = new TemplateParser($context);
        $configuration = new Configuration();
        $configuration->setFeatureState(Configuration::FEATURE_RUNTIME_CACHE, false);
        $string = __DIR__ . '/../../../Fixtures/Atoms/testAtom.html';
        $instance = $subject->parseFile($string, $configuration);
        $this->assertInstanceOf(EntryNode::class, $instance);
    }

    /**
     * @test
     */
    public function getComponentBeingParsedReturnsNullByDefault(): void
    {
        $context = new RenderingContextFixture();
        $subject = new TemplateParser($context);
        $this->assertNull($subject->getComponentBeingParsed());
    }
}
