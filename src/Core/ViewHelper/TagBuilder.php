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
    }

    public function addAttribute(string $attributeName, $attributeValue): void
    {
        $this->attributes[$attributeName] = $attributeValue;
    }

    public function addAttributes(iterable $attributes): void
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->addAttribute($attributeName, $attributeValue);
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
        $output .= $this->renderAttributes($this->attributes);
        if ($this->hasContent() || $this->forceClosingTag) {
            $output .= '>' . $this->content . '</' . $this->tagName . '>';
        } else {
            $output .= ' />';
        }
        return $output;
    }

    protected function renderAttributes(iterable $attributes, ?string $prefix = null): string
    {
        $output = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            if (is_array($attributeValue) || $attributeValue instanceof \Traversable) {
                if ($this->ignoreEmptyAttributes && empty($attributeValue)) {
                    continue;
                }
                $output .= $this->renderAttributes($attributeValue, $prefix . $attributeName . '-');
            } elseif ($this->ignoreEmptyAttributes && trim((string) $attributeValue) === '') {
                continue;
            } else {
                $attributeValue = htmlspecialchars((string) $attributeValue);
                $output .= ' ' . $prefix . $attributeName . '="' . $attributeValue . '"';
            }
        }
        return $output;
    }
}
