<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Argument\ArgumentCollection;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Names of all registered tag attributes
     *
     * @var array
     */
    static private $tagAttributes = [];

    /**
     * Tag builder instance
     *
     * @var TagBuilder
     * @api
     */
    protected $tag = null;

    /**
     * Name of the tag to be created by this view helper
     *
     * @var string
     * @api
     */
    protected $tagName = 'div';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tag = new TagBuilder($this->tagName);
    }

    /**
     * Constructor
     *
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.');
        $this->registerArgument('data', 'array', 'Additional data-* attributes. They will each be added with a "data-" prefix.');
    }

    public function execute(RenderingContextInterface $renderingContext, ?ArgumentCollection $arguments = null)
    {
        $parameters = ($arguments ?? $this->getArguments())->setRenderingContext($renderingContext);
        foreach ($parameters as $argumentName => $argumentValue) {
            if (strpos($argumentName, 'data-') === 0) {
                $this->tag->addAttribute($argumentName, $argumentValue);
                unset($parameters[$argumentName]);
            }
        }

        if (isset($parameters['additionalAttributes']) && is_array($parameters['additionalAttributes'])) {
            $this->tag->addAttributes($parameters['additionalAttributes']);
        }

        if (isset($parameters['data']) && is_array($parameters['data'])) {
            foreach ($parameters['data'] as $dataAttributeKey => $dataAttributeValue) {
                $this->tag->addAttribute('data-' . $dataAttributeKey, (string) $dataAttributeValue);
            }
        }

        if (isset(self::$tagAttributes[get_class($this)])) {
            foreach (self::$tagAttributes[get_class($this)] as $attributeName) {
                if ($this->hasArgument($attributeName) && $parameters[$attributeName] !== '') {
                    $this->tag->addAttribute($attributeName, (string) $parameters[$attributeName]);
                }
            }
        }
        return parent::execute($renderingContext, $arguments);
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
     * @api
     */
    protected function registerTagAttribute(string $name, string $type, string $description, bool $required = false, $defaultValue = null)
    {
        $this->registerArgument($name, $type, $description, $required, $defaultValue);
        self::$tagAttributes[get_class($this)][$name] = $name;
    }

    /**
     * Registers all standard HTML universal attributes.
     * Should be used inside registerArguments();
     *
     * @return void
     * @api
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
