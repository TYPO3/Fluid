<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

use InvalidArgumentException;

/**
 * Tag builder. Can be easily accessed in AbstractTagBasedViewHelper
 *
 * @api
 */
class TagBuilder
{
    /**
     * Name of the Tag to be rendered
     */
    protected string $tagName = '';

    /**
     * Content of the tag to be rendered
     */
    protected ?string $content = '';

    /**
     * Attributes of the tag to be rendered
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Specifies whether this tag needs a closing tag.
     * E.g. <textarea> cant be self-closing even if its empty
     */
    protected bool $forceClosingTag = false;

    protected bool $ignoreEmptyAttributes = false;

    /**
     * Constructor
     *
     * @param string $tagName name of the tag to be rendered
     * @param string|null $tagContent content of the tag to be rendered
     * @api
     */
    public function __construct(string $tagName = '', ?string $tagContent = '')
    {
        $this->setTagName($tagName);
        $this->setContent($tagContent);
    }

    /**
     * Sets the tag name
     *
     * @param string $tagName name of the tag to be rendered
     * @api
     */
    public function setTagName(string $tagName): void
    {
        $this->tagName = $tagName;
    }

    /**
     * Gets the tag name
     *
     * @return string tag name of the tag to be rendered
     * @api
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Sets the content of the tag
     *
     * @param string|null $tagContent content of the tag to be rendered
     * @api
     */
    public function setContent(?string $tagContent): void
    {
        $this->content = $tagContent;
    }

    /**
     * Gets the content of the tag
     *
     * @return string|null content of the tag to be rendered
     * @api
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Returns true if tag contains content
     *
     * @return bool true if tag contains text
     * @api
     */
    public function hasContent(): bool
    {
        return $this->content !== '' && $this->content !== null;
    }

    /**
     * Set this to true to force a closing tag
     * E.g. <textarea> cant be self-closing even if its empty
     *
     * @api
     */
    public function forceClosingTag(bool $forceClosingTag): void
    {
        $this->forceClosingTag = $forceClosingTag;
    }

    /**
     * Returns true if the tag has an attribute with the given name
     *
     * @param string $attributeName name of the attribute
     * @return bool true if the tag has an attribute with the given name
     * @api
     */
    public function hasAttribute(string $attributeName): bool
    {
        return array_key_exists($attributeName, $this->attributes);
    }

    /**
     * Get an attribute from the $attributes-collection
     *
     * @param string $attributeName name of the attribute
     * @return string|null The attribute value or null if the attribute is not registered
     * @api
     */
    public function getAttribute(string $attributeName): ?string
    {
        if (!$this->hasAttribute($attributeName)) {
            return null;
        }
        return $this->attributes[$attributeName];
    }

    /**
     * Get all attribute from the $attributes-collection
     *
     * @return array Attributes indexed by attribute name
     * @api
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function ignoreEmptyAttributes(bool $ignoreEmptyAttributes): void
    {
        $this->ignoreEmptyAttributes = $ignoreEmptyAttributes;
        if ($ignoreEmptyAttributes) {
            $this->attributes = array_filter($this->attributes, function ($item) { return trim((string)$item) !== ''; });
        }
    }

    /**
     * Adds an attribute to the $attributes-collection
     *
     * @param string $attributeName name of the attribute to be added to the tag. Be extremely
     *                              careful if this value is user-provided input!
     * @param string|bool|\Traversable|array|\UnitEnum|\BackedEnum|null $attributeValue attribute value, can only be array or traversable
     *                                                       if the attribute name is either "data" or "area". In
     *                                                       that special case, multiple attributes will be created
     *                                                       with either "data-" or "area-" as prefix
     * @param bool $escapeSpecialCharacters apply htmlspecialchars to attribute value
     * @api
     */
    public function addAttribute(string $attributeName, $attributeValue, bool $escapeSpecialCharacters = true): void
    {
        // Limit attribute names to ASCII characters to keep validation reasonably simple
        // The regular expression lists all printable ASCII characters (0x20 to 0x7F) more or
        // less in the order they are defined in the standard.
        // The following characters are excluded and thus not allowed in attribute names to prevent
        // certain XSS security issues:
        // - Space and Delete character
        // - Single (') and double quotes (")
        // - Less than (<) and greater than (>)
        // - Equals sign (=)
        // - Forward slash (/)
        // - Ampersand (&)
        // Please note that we cannot fully prevent XSS here because browsers interpret the
        // value of certain attributes prefixed with "on" (e. g. "onclick") as JavaScript,
        // which might even be desired functionality.
        // Please be extremely careful when using user-provided content as attribute name!
        if (preg_match('/[^0-9A-Za-z!#\$%()*+,\.:;?@\\[\]\^_`{|}~-]/', $attributeName)) {
            throw new InvalidArgumentException('Invalid attribute name provided: ' . $attributeName, 1721982367);
        }

        if (is_iterable($attributeValue)) {
            if (!in_array($attributeName, ['data', 'aria'], true)) {
                throw new \InvalidArgumentException(
                    sprintf('Value of tag attribute "%s" cannot be of type array.', $attributeName),
                    1709565127,
                );
            }

            foreach ($attributeValue as $name => $value) {
                $this->addAttribute($attributeName . '-' . $name, $value, $escapeSpecialCharacters);
            }
        } else {
            // This should probably also check for null, but we can't do that for now because of backwards compatibility
            if ($attributeValue === false) {
                $this->removeAttribute($attributeName);
                return;
            }

            if ($attributeValue === true) {
                $attributeValue = $attributeName;
            }

            if ($attributeValue instanceof \BackedEnum) {
                $attributeValue = (string)$attributeValue->value;
            } elseif ($attributeValue instanceof \UnitEnum) {
                $attributeValue = $attributeValue->name;
            }

            if (trim((string)$attributeValue) === '' && $this->ignoreEmptyAttributes) {
                return;
            }
            if ($escapeSpecialCharacters) {
                $attributeValue = htmlspecialchars((string)$attributeValue);
            }
            $this->attributes[$attributeName] = $attributeValue;
        }
    }

    /**
     * Adds attributes to the $attributes-collection
     *
     * @param array $attributes collection of attributes to add. key = attribute name, value = attribute value
     * @param bool $escapeSpecialCharacters apply htmlspecialchars to attribute values
     * @api
     */
    public function addAttributes(array $attributes, bool $escapeSpecialCharacters = true): void
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->addAttribute($attributeName, $attributeValue, $escapeSpecialCharacters);
        }
    }

    /**
     * Removes an attribute from the $attributes-collection
     *
     * @param string $attributeName name of the attribute to be removed from the tag
     * @api
     */
    public function removeAttribute(string $attributeName): void
    {
        unset($this->attributes[$attributeName]);
    }

    /**
     * Resets the TagBuilder by setting all members to their default value
     *
     * @api
     */
    public function reset(): void
    {
        $this->tagName = '';
        $this->content = '';
        $this->attributes = [];
        $this->forceClosingTag = false;
    }

    /**
     * Renders and returns the tag
     *
     * @api
     */
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
