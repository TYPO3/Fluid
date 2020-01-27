<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Tag based view helper.
 * Should be used as the base class for all view helpers which output simple tags, as it provides some
 * convenience methods to register default attributes, ...
 *
 * DEPRECATION INFORMATION
 *
 * This utility base class is deprecated in favor of the following strategy, which can be used in any
 * type of ViewHelper:
 *
 * - Avoid declaring attributes, declare only arguments
 * - Set escapeOutput = false in the ViewHelper's class properties.
 * - If you want to pass through any arbitrary tag attribute, override method
 *   allowUndeclaredArgument(string $name) in your ViewHelper and return true.
 * - If you want to only pass through some, use for example in_array() in the
 *   method to return true only for some argument names.
 * - If you need the attribute to be required, declare it as argument and then
 *   manually assign it as attribute.
 * - To render a tag, create an instance of Fluid's TagBuilder and call:
 *   $tagBuilder->addAttributes($this->arguments->getUndeclaredArgumentAndValues())
 *   from your ViewHelper's render method and return $tagBuilder->render().
 *
 * This causes Fluid to select all the arguments that have no argument definition
 * to be assigned as tag attributes.
 *
 * A complete usage example:
 *
 *     // Example: ViewHelper declared "class" as required argument and needs to
 *     // output a DIV tag.
 *     $tagBuilder = new TagBuilder('div')
 *     $tagBuilder->addAttributes($this->arguments->getUndeclaredArgumentsAndValues());
 *     $tagBuilder->addAttribute('class', $this->arguments['class']);
 *     $tagBuilder->setContent($this->renderChildren());
 *     $tag = $tagBuilder->render();
 *     return $tag;
 *
 * The benefit being that:
 *
 * - You no longer have to restrict yourself to a specific parent class in order
 *   to gain tag rendering ability.
 * - Tag attributes no longer have to be declared as arguments.
 * - There is no set of "universal tag attributes"; Fluid is no longer opinionated
 *   about the specific nature of the markup Fluid generates *except* that it can
 *   generate (X)HTML tags.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
abstract class AbstractTagBasedViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    static private $tagAttributes = [];

    /**
     * Tag builder instance
     *
     * @var TagBuilder
     */
    protected $tag = null;

    protected $tagName = 'div';

    public function __construct()
    {
        $this->tag = new TagBuilder($this->tagName);
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.', false, []);
        $this->registerArgument('data', 'array', 'Additional data-* attributes. They will each be added with a "data-" prefix.', false, []);
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments();
        $arguments->setRenderingContext($renderingContext);

        $parameters = $arguments->getArrayCopy();

        if (isset(self::$tagAttributes[get_class($this)])) {
            foreach (self::$tagAttributes[get_class($this)] as $attributeName) {
                if ($this->hasArgument($attributeName) && $parameters[$attributeName] !== '') {
                    $this->tag->addAttribute($attributeName, (string) $parameters[$attributeName]);
                }
            }
        }

        $this->tag->addAttributes($parameters['additionalAttributes'] ?? []);
        $this->tag->addAttributes(['data' => $parameters['data']]);
        $this->tag->addAttributes($arguments->getUndeclaredArgumentsAndValues());

        return parent::evaluate($renderingContext);
    }

    public function render()
    {
        return $this->tag->render();
    }

    /**
     * Register a new tag attribute. Tag attributes are all arguments which will be directly appended to a tag if you call $this->initializeTag()
     *
     * @param string $name Name of tag attribute
     * @param string $type Type of the tag attribute
     * @param string $description Description of tag attribute
     * @param boolean $required set to TRUE if tag attribute is required. Defaults to FALSE.
     * @param mixed $defaultValue Optional, default value of attribute if one applies
     * @return void
     */
    protected function registerTagAttribute(string $name, string $type, string $description, bool $required = false, $defaultValue = null)
    {
        $this->registerArgument($name, $type, $description, $required, $defaultValue);
        self::$tagAttributes[get_class($this)][$name] = $name;
    }

    public function allowUndeclaredArgument(string $argumentName): bool
    {
        // We only allow arbitrary arguments w/o a definition, if they are prefixed with "data-".
        return strncmp('data-', $argumentName, 5) === 0 || parent::allowUndeclaredArgument($argumentName);
    }

    /**
     * Registers all standard HTML universal attributes.
     * Should be used inside registerArguments();
     *
     * @return void
     */
    protected function registerUniversalTagAttributes()
    {
        $this->registerTagAttribute('class', 'string', 'CSS class(es) for this element');
        $this->registerTagAttribute('dir', 'string', 'Text direction for this HTML element. Allowed strings: "ltr" (left to right), "rtl" (right to left)');
        $this->registerTagAttribute('id', 'string', 'Unique (in this file) identifier for this HTML element.');
        $this->registerTagAttribute('lang', 'string', 'Language for this element. Use short names specified in RFC 1766');
        $this->registerTagAttribute('style', 'string', 'Individual CSS styles for this element');
        $this->registerTagAttribute('title', 'string', 'Tooltip text of element');
        $this->registerTagAttribute('accesskey', 'string', 'Keyboard shortcut to access this element');
        $this->registerTagAttribute('tabindex', 'integer', 'Specifies the tab order of this element');
        $this->registerTagAttribute('onclick', 'string', 'JavaScript evaluated for the onclick event');
    }
}
