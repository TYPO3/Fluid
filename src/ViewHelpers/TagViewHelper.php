<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\TagGenerating;

/**
 * Tag Generating ViewHelper
 *
 * Generates a single HTML/XML tag with an optional tag name
 * and arbitrary attributes. Supports the special `data` attribute
 * which can be an array of sub-attributes that get expanded to
 * `data-attributename` format and added as tag attribute.
 * You can combine the `data` attribute with any number of `data-`
 * prefixed attributes. Specific attributes take priority over array.
 *
 * Example:
 *
 *     <!-- Declare array of data-prefixed attributes -->
 *     <f:variable name="myDataArray" value="{foo: 'foo', bar: 'bar'}" />
 *     <f:tag tag="pre" data="{myDataArray}" data-bar="priority">Content of tag</f:tag>
 *
 * Output:
 *
 *     <pre data-foo="foo" data-bar="priority">Content of tag</pre>
 */
class TagViewHelper extends AbstractViewHelper
{
    use TagGenerating;
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('content', 'string', 'Tag name (default "div")', false, '');
        parent::initializeArguments();
        $this->registerArgument('tag', 'string', 'Tag name (default "div")', false, 'div');
        $this->registerArgument('forceClosingTag', 'boolean', 'Force a closing tag (disallow self-closing). Off by default.', false, false);
        $this->registerArgument('ignoreEmptyAttributes', 'boolean', 'Ignores attributes with empty values (zero not regarded as empty!). On by default.', false, true);
    }

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
        return (new TagBuilder($arguments['tag'], $renderChildrenClosure))
            ->addAttributes($attributes)
            ->forceClosingTag($arguments['forceClosingTag'])
            ->ignoreEmptyAttributes($arguments['ignoreEmptyAttributes']);
    }
}
