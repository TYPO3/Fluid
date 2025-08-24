<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Tag based view helper.
 * Should be used as the base class for all view helpers which output simple tags, as it provides some
 * convenience methods to register default attributes, ...
 *
 * @api
 */
abstract class AbstractTagBasedViewHelper extends AbstractViewHelper
{
    /**
     * Disable escaping of tag based ViewHelpers so that the rendered tag is not htmlspecialchar'd
     */
    protected bool $escapeOutput = false;

    /**
     * Tag builder instance
     *
     * @api
     */
    protected TagBuilder $tag;

    /**
     * Name of the tag to be created by this view helper
     *
     * @var string
     * @api
     */
    protected string $tagName = 'div';

    /**
     * Arguments which are valid but do not have an ArgumentDefinition, e.g.
     * data- prefixed arguments.
     *
     * @var array<string, mixed>
     */
    protected array $additionalArguments = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setTagBuilder(new TagBuilder($this->tagName));
    }

    /**
     * @param TagBuilder $tag
     */
    public function setTagBuilder(TagBuilder $tag): void
    {
        $this->tag = $tag;
        $this->tag->setTagName($this->tagName);
    }

    /**
     * Constructor
     *
     * @api
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.');
        $this->registerArgument('data', 'array', 'Additional data-* attributes. They will each be added with a "data-" prefix.');
        $this->registerArgument('aria', 'array', 'Additional aria-* attributes. They will each be added with a "aria-" prefix.');
    }

    /**
     * Sets the tag name to $this->tagName.
     * Additionally, sets all tag attributes which were registered in
     * additionalArguments.
     *
     * Will be invoked just before the render method.
     *
     * @api
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->tag->reset();
        $this->tag->setTagName($this->tagName);

        if ($this->hasArgument('additionalAttributes') && is_array($this->arguments['additionalAttributes'])) {
            $this->tag->addAttributes($this->arguments['additionalAttributes']);
        }

        if ($this->hasArgument('data') && is_array($this->arguments['data'])) {
            foreach ($this->arguments['data'] as $dataAttributeKey => $dataAttributeValue) {
                $this->tag->addAttribute('data-' . $dataAttributeKey, $dataAttributeValue);
            }
        }

        if ($this->hasArgument('aria') && is_array($this->arguments['aria'])) {
            foreach ($this->arguments['aria'] as $ariaAttributeKey => $ariaAttributeValue) {
                $this->tag->addAttribute('aria-' . $ariaAttributeKey, $ariaAttributeValue);
            }
        }

        foreach ($this->additionalArguments as $argumentName => $argumentValue) {
            // This condition is left here for compatibility reasons. Removing this will be a breaking change
            // because TagBuilder renders empty strings as empty attributes (as it should be). We might remove
            // this condition in the future to have a clean solution.
            if ($argumentValue !== null && $argumentValue !== '') {
                $this->tag->addAttribute($argumentName, $argumentValue);
            }
        }
    }

    public function handleAdditionalArguments(array $arguments): void
    {
        $this->additionalArguments = $arguments;
        parent::handleAdditionalArguments($arguments);
    }

    public function validateAdditionalArguments(array $arguments): void
    {
        // Skip validation of additional arguments since we want to pass all arguments to the tag
    }

    public function render(): string
    {
        return $this->tag->render();
    }
}
