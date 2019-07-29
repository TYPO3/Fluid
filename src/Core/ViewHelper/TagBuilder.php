<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Tag builder. Can be easily accessed in AbstractTagBasedViewHelper
 */
class TagBuilder
{
    protected $tagName = '';

    protected $content = '';

    protected $attributes = [];

    protected $forceClosingTag = false;

    protected $ignoreEmptyAttributes = false;

    public function __construct(string $tagName = '', string $tagContent = '')
    {
        $this->setTagName($tagName);
        $this->setContent($tagContent);
    }

    public function setTagName(string $tagName): void
    {
        $this->tagName = $tagName;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function setContent(?string $tagContent): void
    {
        $this->content = $tagContent;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function hasContent(): bool
    {
        return $this->content !== '' && $this->content !== null;
    }

    public function forceClosingTag(bool $forceClosingTag): void
    {
        $this->forceClosingTag = $forceClosingTag;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return array_key_exists($attributeName, $this->attributes);
    }

    public function getAttribute(string $attributeName)
    {
        if (!$this->hasAttribute($attributeName)) {
            return null;
        }
        return $this->attributes[$attributeName];
    }

    public function getAttributes(): iterable
    {
        return $this->attributes;
    }

    public function ignoreEmptyAttributes(bool $ignoreEmptyAttributes): void
    {
        $this->ignoreEmptyAttributes = $ignoreEmptyAttributes;
        if ($ignoreEmptyAttributes) {
            $this->attributes = array_filter($this->attributes, function ($item): bool { return trim((string) $item) !== ''; });
        }
    }

    public function addAttribute(string $attributeName, $attributeValue, bool $escapeSpecialCharacters = true): void
    {
        if ($attributeName === 'data' && (is_array($attributeValue) || $attributeValue instanceof \Traversable)) {
            foreach ($attributeValue as $name => $value) {
                $this->addAttribute('data-' . $name, $value, $escapeSpecialCharacters);
            }
        } else {
            $attributeValue = (string) $attributeValue;
            if (trim($attributeValue) === '' && $this->ignoreEmptyAttributes) {
                return;
            }
            if ($escapeSpecialCharacters) {
                $attributeValue = htmlspecialchars($attributeValue);
            }
            $this->attributes[$attributeName] = $attributeValue;
        }
    }

    public function addAttributes(iterable $attributes, bool $escapeSpecialCharacters = true): void
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->addAttribute($attributeName, (string) $attributeValue, $escapeSpecialCharacters);
        }
    }

    public function removeAttribute(string $attributeName): void
    {
        unset($this->attributes[$attributeName]);
    }

    public function reset(): void
    {
        $this->tagName = '';
        $this->content = '';
        $this->attributes = [];
        $this->forceClosingTag = false;
    }

    public function render(): string
    {
        if (empty($this->tagName)) {
            return '';
        }
        $output = '<' . $this->tagName;
        foreach ($this->attributes as $attributeName => $attributeValue) {
            $output .= ' ' . $attributeName . '="' . $attributeValue . '"';
        }
        if ($this->hasContent() || $this->forceClosingTag) {
            $output .= '>' . $this->content . '</' . $this->tagName . '>';
        } else {
            $output .= ' />';
        }
        return $output;
    }
}
