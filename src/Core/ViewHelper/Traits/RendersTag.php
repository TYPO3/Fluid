<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Compiler\ViewHelperCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

/**
 * Class RendersTag
 *
 * Provides a public contract for ViewHelpers which
 * generate tags (substitute for TagBasedViewHelper).
 */
trait RendersTag
{
    /**
     * Disable escaping of tag based ViewHelpers so that the rendered tag is not htmlspecialchar'd
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var array
     */
    static protected $tagAttributes = [];

    /**
     * @var array
     */
    protected $extraArguments = [];

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.');
        $this->registerArgument('data', 'array', 'Additional data-* attributes. They will each be added with a "data-" prefix.');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return string Rendered string
     * @api
     */
    public function render()
    {
        $attributes = $this->getTagAttributes();
        foreach ($attributes as $attributeName => &$attributeValue) {
            if (isset($this->arguments[$attributeName])) {
                $attributeValue = $this->arguments[$attributeName];
            }
        }
        return static::renderTag(
            TagBuilder::create($this->getTagName(), $this->renderChildren(), $attributes),
            $this->arguments,
            $attributes,
            $this->renderingContext
        );
    }

    /**
     * @param TagBuilder $tag
     * @param array $arguments
     * @param array $attributes
     * @param RenderingContextInterface $renderingContext
     */
    public static function renderTag(
        TagBuilder $tag,
        array $arguments,
        array $attributes,
        RenderingContextInterface $renderingContext
    ) {
        return $tag->render();
    }

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        $tagAttributeVariable = $compiler->variableName('tagAttributes');
        $tagAttributes = $this->getTagAttributes();
        $argumentDefinitions = $this->prepareArguments();
        $initializationPhpCode .= sprintf('%s = [];' . PHP_EOL, $tagAttributeVariable);
        if (!empty($tagAttributes['data'])) {
            $tagAttributes += array_map(function($string) { return 'data-' . $string; }, $tagAttributes['data']);
            unset($tagAttributes['data']);
        }
        foreach ($tagAttributes as $attributeName => $_) {
            $initializationPhpCode .= sprintf(
                '%s[\'%s\'] = %s[\'%s\'];' . PHP_EOL,
                $tagAttributeVariable,
                $attributeName,
                $argumentsName,
                $attributeName
            );
        }

        return sprintf(
            '%s::create(\'%s\', %s())->addAttributes(%s)->render();' . PHP_EOL,
            TagBuilder::class,
            $this->getTagName(),
            $closureName,
            $tagAttributeVariable
        );
    }

    /**
     *
     * @throws Exception
     * @param array $arguments
     * @return void
     */
    public function handleAdditionalArguments(array $arguments)
    {
        $this->extraArguments = $arguments;
    }

    /**
     * Default implementation of validating additional, undeclared arguments.
     * In this implementation the behavior is to consistently throw an error
     * about NOT supporting any additional arguments. This method MUST be
     * overridden by any ViewHelper that desires this support and this inherited
     * method must not be called, obviously.
     *
     * @throws Exception
     * @param array $arguments
     * @return void
     */
    public function validateAdditionalArguments(array $arguments)
    {
        $unassigned = [];
        foreach ($arguments as $argumentName => $argumentValue) {
            if (strpos($argumentName, 'data-') === 0) {
                $this->extraArguments[$argumentName] = $argumentValue;
            } else {
                $unassigned[$argumentName] = $argumentName;
            }
        }
        if (!empty($unassigned)) {
            throw new Exception(
                sprintf(
                    'Undeclared arguments passed to ViewHelper %s: %s',
                    get_class($this),
                    implode(', ', array_keys($unassigned))
                )
            );
        }
    }

    /**
     * @return \Closure
     */
    protected abstract function buildRenderChildrenClosure();

    /**
     * @return mixed
     */
    protected abstract function renderChildren();

    /**
     * @return mixed
     */
    public abstract function prepareArguments();

    /**
     * Register a new tag attribute. Tag attributes are all arguments, but are delivered to the renderTag
     * method as a separate array (which is already assigned as tag attributes).
     *
     * @param string $name Name of tag attribute
     * @param string $type Type of the tag attribute
     * @param string $description Description of tag attribute
     * @param boolean $required set to TRUE if tag attribute is required. Defaults to FALSE.
     * @param mixed $defaultValue Optional, default value of attribute if one applies
     * @return void
     * @api
     */
    protected function registerTagAttribute($name, $type, $description, $required = false, $defaultValue = null)
    {
        $this->registerArgument($name, $type, $description, $required, $defaultValue);
        static::$tagAttributes[static::class][$name] = $defaultValue;
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

    /**
     * @return string[]
     */
    protected function getTagAttributes()
    {
        $base = static::$tagAttributes[static::class];
        if (!empty($this->arguments['data'])) {
            $base += array_map(function($string) { return 'data-' . $string; }, $this->arguments['data']);
        }
        $base += $this->extraArguments;
        foreach ($this->arguments as $argumentName => $_) {
            if (strpos($argumentName, 'data-') === 0) {
                $base[$argumentName] = $argumentName;
            }
        }
        return $base;
    }

    /**
     * @return string
     */
    protected function getTagName()
    {
        if (isset($this->tagName)) {
            return $this->tagName;
        }
        return 'div';
    }
}
