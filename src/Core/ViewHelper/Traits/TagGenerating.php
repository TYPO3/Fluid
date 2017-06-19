<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Tag Generating ViewHelper Trait
 *
 * Implement in ViewHelpers which should generate tags.
 */
trait TagGenerating
{
    /**
     * @return ArgumentDefinition[]
     */
    abstract public function prepareArguments();

    /**
     * @param array $arguments
     * @param array $attributes
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return TagBuilder
     */
    public static function createTag(
        array $arguments,
        array $attributes,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): TagBuilder {
        return (new TagBuilder('div', $renderChildrenClosure))
            ->addAttributes($attributes + (isset($arguments['additionalAttributes']) ? $arguments['additionalAttributes'] : []));
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        return static::createTag(
            $arguments,
            array_diff_key($arguments, $renderingContext->getViewHelperResolver()->getArgumentDefinitionsForViewHelper(new static)),
            $renderChildrenClosure,
            $renderingContext
        )->render();
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function handleAdditionalArguments(array $arguments)
    {
    }

    /**
     * @param array $arguments
     * @return void
     */
    public function validateAdditionalArguments(array $arguments)
    {
    }
}