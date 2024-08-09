<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

final class TagBuilderTest extends TestCase
{
    #[Test]
    public function constructorSetsTagName(): void
    {
        $tagBuilder = new TagBuilder('someTagName');
        self::assertEquals('someTagName', $tagBuilder->getTagName());
    }

    #[Test]
    public function constructorSetsTagContent(): void
    {
        $tagBuilder = new TagBuilder('', '<some text>');
        self::assertEquals('<some text>', $tagBuilder->getContent());
    }

    #[Test]
    public function setContentDoesNotEscapeValue(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('<to be escaped>');
        self::assertEquals('<to be escaped>', $tagBuilder->getContent());
    }

    #[Test]
    public function hasContentReturnsTrueIfTagContainsText(): void
    {
        $tagBuilder = new TagBuilder('', 'foo');
        self::assertTrue($tagBuilder->hasContent());
    }

    #[Test]
    public function hasContentReturnsFalseIfContentIsNull(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent(null);
        self::assertFalse($tagBuilder->hasContent());
    }

    #[Test]
    public function hasContentReturnsFalseIfContentIsAnEmptyString(): void
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('');
        self::assertFalse($tagBuilder->hasContent());
    }

    #[Test]
    public function renderReturnsEmptyStringByDefault(): void
    {
        $tagBuilder = new TagBuilder();
        self::assertEquals('', $tagBuilder->render());
    }

    #[Test]
    public function renderReturnsSelfClosingTagIfNoContentIsSpecified(): void
    {
        $tagBuilder = new TagBuilder('tag');
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    #[Test]
    public function contentCanBeRemoved(): void
    {
        $tagBuilder = new TagBuilder('tag', 'some content');
        $tagBuilder->setContent(null);
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    #[Test]
    public function renderReturnsOpeningAndClosingTagIfNoContentIsSpecifiedButForceClosingTagIsTrue(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->forceClosingTag(true);
        self::assertEquals('<tag></tag>', $tagBuilder->render());
    }

    #[Test]
    public function attributesAreProperlyRendered(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute1="attribute1value" attribute2="attribute2value" attribute3="attribute3value" />', $tagBuilder->render());
    }

    #[Test]
    public function arrayAttributesAreProperlyRendered(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('data', ['attribute1' => 'data1', 'attribute2' => 'data2']);
        $tagBuilder->addAttribute('aria', ['attribute1' => 'aria1']);
        self::assertEquals('<tag data-attribute1="data1" data-attribute2="data2" aria-attribute1="aria1" />', $tagBuilder->render());
    }

    #[Test]
    public function customArrayAttributesThrowException(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionCode(1709565127);
        self::expectExceptionMessage('Value of tag attribute "custom" cannot be of type array.');
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('custom', ['attribute1' => 'data1', 'attribute2' => 'data2']);
    }

    #[Test]
    public function attributeValuesAreEscapedByDefault(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<to be escaped>');
        self::assertEquals('<tag foo="&lt;to be escaped&gt;" />', $tagBuilder->render());
    }

    #[Test]
    public function attributeValuesAreNotEscapedIfDisabled(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<not to be escaped>', false);
        self::assertEquals('<tag foo="<not to be escaped>" />', $tagBuilder->render());
    }

    #[Test]
    public function attributesCanBeRemoved(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        $tagBuilder->removeAttribute('attribute2');
        self::assertEquals('<tag attribute1="attribute1value" attribute3="attribute3value" />', $tagBuilder->render());
    }

    #[Test]
    public function emptyAttributesGetRemovedWhenCallingIgnoreEmptyAttributesWithTrue(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        $tagBuilder->ignoreEmptyAttributes(true);
        self::assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
    }

    #[Test]
    public function emptyAttributesGetPreservedWhenCallingIgnoreEmptyAttributesWithFalse(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->ignoreEmptyAttributes(false);
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute1="" attribute2="" attribute3="attribute3value" />', $tagBuilder->render());
    }

    #[Test]
    public function ignoresNewEmptyAttributesIfEmptyAttributesIgnored(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->ignoreEmptyAttributes(true);
        $tagBuilder->addAttribute('attribute1', '');
        $tagBuilder->addAttribute('attribute2', '');
        $tagBuilder->addAttribute('attribute3', 'attribute3value');
        self::assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
    }

    #[Test]
    public function attributesCanBeAccessed(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValue = $tagBuilder->getAttribute('attribute1');
        self::assertSame('attribute1value', $attributeValue);
    }

    #[Test]
    public function attributesCanBeAccessedBulk(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValues = $tagBuilder->getAttributes();
        self::assertEquals(['attribute1' => 'attribute1value'], $attributeValues);
    }

    #[Test]
    public function getAttributeWithMissingAttributeReturnsNull(): void
    {
        $tagBuilder = new TagBuilder('tag');
        $attributeValue = $tagBuilder->getAttribute('missingattribute');
        self::assertNull($attributeValue);
    }

    #[Test]
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

    #[Test]
    public function tagNameCanBeOverridden(): void
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('bar');
        self::assertEquals('<bar />', $tagBuilder->render());
    }

    #[Test]
    public function tagContentCanBeOverridden(): void
    {
        $tagBuilder = new TagBuilder('foo', 'some content');
        $tagBuilder->setContent('');
        self::assertEquals('<foo />', $tagBuilder->render());
    }

    #[Test]
    public function tagIsNotRenderedIfTagNameIsEmpty(): void
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('');
        self::assertEquals('', $tagBuilder->render());
    }

    public static function handlesBooleanAttributesCorrectlyDataProvider(): array
    {
        return [
            'value false' => [false, '<foo />'],
            'value true' => [true, '<foo async="async" />'],
            'value null' => [null, '<foo async="" />'],
            'string false' => ['false', '<foo async="false" />'],
            'string true' => ['true', '<foo async="true" />'],
            'string null' => ['null', '<foo async="null" />'],
            'atttribute name' => ['async', '<foo async="async" />'],
        ];
    }

    #[DataProvider('handlesBooleanAttributesCorrectlyDataProvider')]
    #[Test]
    public function handlesBooleanAttributesCorrectly(mixed $attributeValue, string $expected): void
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->addAttribute('async', $attributeValue);
        self::assertEquals($expected, $tagBuilder->render());
    }
}
