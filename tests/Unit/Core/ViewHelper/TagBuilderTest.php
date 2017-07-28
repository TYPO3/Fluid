<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Testcase for TagBuilder
 */
class TagBuilderTest extends UnitTestCase
{

    /**
     * @test
     */
    public function constructorSetsTagName()
    {
        $tagBuilder = new TagBuilder('someTagName');
        $this->assertEquals('someTagName', $tagBuilder->getTagName());
    }

    /**
     * @test
     */
    public function constructorSetsTagContent()
    {
        $tagBuilder = new TagBuilder('', '<some text>');
        $this->assertEquals('<some text>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function setContentDoesNotEscapeValue()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('<to be escaped>', false);
        $this->assertEquals('<to be escaped>', $tagBuilder->getContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsTrueIfTagContainsText()
    {
        $tagBuilder = new TagBuilder('', 'foo');
        $this->assertTrue($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsNull()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent(null);
        $this->assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function hasContentReturnsFalseIfContentIsAnEmptyString()
    {
        $tagBuilder = new TagBuilder();
        $tagBuilder->setContent('');
        $this->assertFalse($tagBuilder->hasContent());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringByDefault()
    {
        $tagBuilder = new TagBuilder();
        $this->assertEquals('', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsSelfClosingTagIfNoContentIsSpecified()
    {
        $tagBuilder = new TagBuilder('tag');
        $this->assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function contentCanBeRemoved()
    {
        $tagBuilder = new TagBuilder('tag', 'some content');
        $tagBuilder->setContent(null);
        $this->assertEquals('<tag />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function renderReturnsOpeningAndClosingTagIfNoContentIsSpecifiedButForceClosingTagIsTrue()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->forceClosingTag(true);
        $this->assertEquals('<tag></tag>', $tagBuilder->render());
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
        $this->assertEquals('<tag attribute1="attribute1value" attribute2="attribute2value" attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributeValuesAreEscapedByDefault()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<to be escaped>');
        $this->assertEquals('<tag foo="&lt;to be escaped&gt;" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributeValuesAreNotEscapedIfDisabled()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('foo', '<not to be escaped>', false);
        $this->assertEquals('<tag foo="<not to be escaped>" />', $tagBuilder->render());
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
        $this->assertEquals('<tag attribute1="attribute1value" attribute3="attribute3value" />', $tagBuilder->render());
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
        $this->assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
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
        $this->assertEquals('<tag attribute1="" attribute2="" attribute3="attribute3value" />', $tagBuilder->render());
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
        $this->assertEquals('<tag attribute3="attribute3value" />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function attributesCanBeAccessed()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValue = $tagBuilder->getAttribute('attribute1');
        $this->assertSame('attribute1value', $attributeValue);
    }

    /**
     * @test
     */
    public function attributesCanBeAccessedBulk()
    {
        $tagBuilder = new TagBuilder('tag');
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $attributeValues = $tagBuilder->getAttributes();
        $this->assertEquals(['attribute1' => 'attribute1value'], $attributeValues);
    }

    /**
     * @test
     */
    public function getAttributeWithMissingAttributeReturnsNull()
    {
        $tagBuilder = new TagBuilder('tag');
        $attributeValue = $tagBuilder->getAttribute('missingattribute');
        $this->assertNull($attributeValue);
    }

    /**
     * @test
     */
    public function resetResetsTagBuilder()
    {
        $tagBuilder = $this->getAccessibleMock(TagBuilder::class, ['dummy']);
        $tagBuilder->setTagName('tagName');
        $tagBuilder->setContent('some content');
        $tagBuilder->forceClosingTag(true);
        $tagBuilder->addAttribute('attribute1', 'attribute1value');
        $tagBuilder->addAttribute('attribute2', 'attribute2value');
        $tagBuilder->reset();

        $this->assertEquals('', $tagBuilder->_get('tagName'));
        $this->assertEquals('', $tagBuilder->_get('content'));
        $this->assertEquals([], $tagBuilder->_get('attributes'));
        $this->assertFalse($tagBuilder->_get('forceClosingTag'));
    }

    /**
     * @test
     */
    public function tagNameCanBeOverridden()
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('bar');
        $this->assertEquals('<bar />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagContentCanBeOverridden()
    {
        $tagBuilder = new TagBuilder('foo', 'some content');
        $tagBuilder->setContent('');
        $this->assertEquals('<foo />', $tagBuilder->render());
    }

    /**
     * @test
     */
    public function tagIsNotRenderedIfTagNameIsEmpty()
    {
        $tagBuilder = new TagBuilder('foo');
        $tagBuilder->setTagName('');
        $this->assertEquals('', $tagBuilder->render());
    }
}
