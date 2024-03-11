<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

final class TagBuilderTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructorSetsTagName(): void
    {
        $tagBuilder = new TagBuilder('someTagName');
        self::assertEquals('someTagName', $tagBuilder->getTagName());
    }

    /**
     * @test
     */
    public function constructorSetsTagContent(): void
    {
        $tagBuilder = new TagBuilder('', '<some text>');
        self::assertEquals('<some text>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function setContentDoesNotEscapeValue(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('<to be escaped>');
        self::assertEquals('<to be escaped>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsTrueIfTagContainsText(): void
    {
        $tagBuilder = new TagBuilder('', 'foo');
        self::assertTrue($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsNull(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent(null);
        self::assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsAnEmptyString(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('');
        self::assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringByDefault(): void
    {
        $tagBuilder = new TagBuilder();
        self::assertEquals('', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsSelfClosingTagIfNoContentIsSpecified(): void
    {
        $tagBuilder = new TagBuilder('tag');
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function contentCanBeRemoved(): void
    {
        $tagBuilder = new TagBuilder('tag', 'some content');
        $tagBuilder->setContent(null);
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsOpeningAndClosingTagIfNoContentIsSpecifiedButForceClosingTagIsTrue(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->forceClosingTag(true);
        self::assertEquals('<tag></tag>', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesAreProperlyRendered(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute1="attribute1value" attribute2="attribute2value" attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function arrayAttributesAreProperlyRendered(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('data', ['attribute1' => 'data1', 'attribute2' => 'data2']);
        $tagBuilder->addAttribute('aria', ['attribute1' => 'aria1']);
        self::assertEquals('<tag data-attribute1="data1" data-attribute2="data2" aria-attribute1="aria1" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function customArrayAttributesThrowException(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1709565127);
        self::expectExceptionMessage('Value of tag attribute "custom" cannot be of type array.');
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('custom', ['attribute1' => 'data1', 'attribute2' => 'data2']);
    }

    /**
     * @test
     */
    public function attributeValuesAreEscapedByDefault(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<to be escaped>');
        self::assertEquals('<tag foo="&lt;to be escaped&gt;" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributeValuesAreNotEscapedIfDisabled(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<not to be escaped>', false);
        self::assertEquals('<tag foo="<not to be escaped>" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesCanBeRemoved(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        $tagBuilder->removeAttribute('attribute2');
        self::assertEquals('<tag attribute1="attribute1value" attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function emptyAttributesGetRemovedWhenCallingIgnoreEmptyAttributesWithTrue(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        $tagBuilder->ignoreEmptyAttributes(true);
        self::assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function emptyAttributesGetPreservedWhenCallingIgnoreEmptyAttributesWithFalse(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->ignoreEmptyAttributes(false);
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute1="" attribute2="" attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function ignoresNewEmptyAttributesIfEmptyAttributesIgnored(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->ignoreEmptyAttributes(true);
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesCanBeAccessed(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValue = $tagBuilder->getAttribute('attribute1');
        self::assertSame('attribute1value', $attributeValue);
    }

    /**
     * @test
     */
    public function attributesCanBeAccessedBulk(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValues = $tagBuilder->getAttributes();
        self::assertEquals(['attribute1' => 'attribute1value'], $attributeValues);
    }

    /**
     * @test
     */
    public function getAttributeWithMissingAttributeReturnsNull(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $attributeValue = $tagBuilder->getAttribute('missingattribute');
        self::assertNull($attributeValue);
    }

    /**
     * @test
     */
    public function resetResetsTagBuilder(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setTagName('tagName');
        $tagBuilder->setContent('some content');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->reset();

        self::assertSame('', $tagBuilder->getTagName());
        self::assertSame('', $tagBuilder->getContent());
        self::assertSame([], $tagBuilder->getAttributes());
    }

    /**
     * @test
     */
    public function tagNameCanBeOverridden(): void
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('bar');
        self::assertEquals('<bar />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagContentCanBeOverridden(): void
    {
        $tagBuilder = new TagBuilder('foo', 'some content');
        $tagBuilder->setContent('');
        self::assertEquals('<foo />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagIsNotRenderedIfTagNameIsEmpty(): void
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('');
        self::assertEquals('', $tagBuilder->render());
    }
}
