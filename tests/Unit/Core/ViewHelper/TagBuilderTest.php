<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class TagBuilderTest extends UnitTestCase
{

    /**
     * @test
     */
    public function constructorSetsTagName()
    {
        $tagBuilder = new TagBuilder('someTagName');
        self::assertEquals('someTagName', $tagBuilder->getTagName());
    }

    /**
     * @test
     */
    public function constructorSetsTagContent()
    {
        $tagBuilder = new TagBuilder('', '<some text>');
        self::assertEquals('<some text>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function setContentDoesNotEscapeValue()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('<to be escaped>');
        self::assertEquals('<to be escaped>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsTrueIfTagContainsText()
    {
        $tagBuilder = new TagBuilder('', 'foo');
        self::assertTrue($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsNull()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent(null);
        self::assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsAnEmptyString()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('');
        self::assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringByDefault()
    {
        $tagBuilder = new TagBuilder();
        self::assertEquals('', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsSelfClosingTagIfNoContentIsSpecified()
    {
        $tagBuilder = new TagBuilder('tag');
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function contentCanBeRemoved()
    {
        $tagBuilder = new TagBuilder('tag', 'some content');
        $tagBuilder->setContent(null);
        self::assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsOpeningAndClosingTagIfNoContentIsSpecifiedButForceClosingTagIsTrue()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->forceClosingTag(true);
        self::assertEquals('<tag></tag>', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesAreProperlyRendered()
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
    public function attributeValuesAreEscapedByDefault()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<to be escaped>');
        self::assertEquals('<tag foo="&lt;to be escaped&gt;" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributeValuesAreNotEscapedIfDisabled()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<not to be escaped>', false);
        self::assertEquals('<tag foo="<not to be escaped>" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesCanBeRemoved()
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
    public function emptyAttributesGetRemovedWhenCallingIgnoreEmptyAttributesWithTrue()
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
    public function emptyAttributesGetPreservedWhenCallingIgnoreEmptyAttributesWithFalse()
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
    public function ignoresNewEmptyAttributesIfEmptyAttributesIgnored()
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
    public function attributesCanBeAccessed()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValue = $tagBuilder->getAttribute('attribute1');
        self::assertSame('attribute1value', $attributeValue);
    }

    /**
     * @test
     */
    public function attributesCanBeAccessedBulk()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValues = $tagBuilder->getAttributes();
        self::assertEquals(['attribute1' => 'attribute1value'], $attributeValues);
    }

    /**
     * @test
     */
    public function getAttributeWithMissingAttributeReturnsNull()
    {
        $tagBuilder = new TagBuilder('tag');
        $attributeValue = $tagBuilder->getAttribute('missingattribute');
        self::assertNull($attributeValue);
    }

    /**
     * @test
     */
    public function resetResetsTagBuilder()
    {
        $tagBuilder = $this->getAccessibleMock(TagBuilder::class, []);
        $tagBuilder->setTagName('tagName');
        $tagBuilder->setContent('some content');
        $tagBuilder->forceClosingTag(true);
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->reset();

        self::assertEquals('', $tagBuilder->_get('tagName'));
        self::assertEquals('', $tagBuilder->_get('content'));
        self::assertEquals([], $tagBuilder->_get('attributes'));
        self::assertFalse($tagBuilder->_get('forceClosingTag'));
    }

    /**
     * @test
     */
    public function tagNameCanBeOverridden()
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('bar');
        self::assertEquals('<bar />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagContentCanBeOverridden()
    {
        $tagBuilder = new TagBuilder('foo', 'some content');
        $tagBuilder->setContent('');
        self::assertEquals('<foo />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagIsNotRenderedIfTagNameIsEmpty()
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('');
        self::assertEquals('', $tagBuilder->render());
    }
}
