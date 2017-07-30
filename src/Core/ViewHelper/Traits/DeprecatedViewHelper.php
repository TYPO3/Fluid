<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/**
 * Class DeprecatedViewHelper
 *
 * Contains methods which are deprecated and scheduled for removal.
 */
trait DeprecatedViewHelper
{
    /**
     * ViewHelper Variable Container
     * @var ViewHelperVariableContainer
     * @deprecated Will be removed in Fluid 3.0; use $renderingContext->getViewHelperVariableContainer() instead.
     * @api
     */
    protected $viewHelperVariableContainer;

    /**
     * Current variable container reference.
     * @var VariableProviderInterface
     * @deprecated Will be removed in Fluid 3.0; use $renderingContext->getVariableProvider() instead.
     * @api
     */
    protected $templateVariableContainer;

    /**
     * @var NodeInterface[] array
     * @api
     * @deprecated Will be removed in Fluid 3.0; use $this->viewHelperNode->getChildNodes() instead.
     */
    protected $childNodes = [];

    /**
     * Overridden method which sets deprecated properties.
     *
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext)
    {
        parent::setRenderingContext($renderingContext);
        $this->templateVariableContainer = $renderingContext->getVariableProvider();
        $this->viewHelperVariableContainer = $renderingContext->getViewHelperVariableContainer();
    }

    /**
     * DEPRECATED - is no longer called.
     *
     * This is PURELY INTERNAL! Never override this method!!
     *
     * @deprecated
     * @param NodeInterface[] $childNodes
     * @return void
     */
    public function setChildNodes(array $childNodes)
    {
        $this->childNodes = $childNodes;
    }

    /**
     * DEPRECATED - is no longer called.
     *
     * Resets the ViewHelper state.
     *
     * Overwrite this method if you need to get a clean state of your ViewHelper.
     *
     * @deprecated
     * @return void
     */
    public function resetState()
    {
    }

    /**
     * DEPRECATED - is no longer called.
     *
     * Initializes the ViewHelper. Deprecated since frameworks by the norm provide object lifecycle methods.
     *
     * @deprecated
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * DEPRECATED - is no longer called; handled by ViewHelperArgumentValidator
     *
     * Validate arguments, and throw exception if arguments do not validate.
     *
     * @deprecated
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateArguments()
    {
        $argumentDefinitions = $this->prepareArguments();
        foreach ($argumentDefinitions as $argumentName => $registeredArgument) {
            if ($this->hasArgument($argumentName)) {
                $value = $this->arguments[$argumentName];
                $type = $registeredArgument->getType();
                if ($value !== $registeredArgument->getDefaultValue() && $type !== 'mixed') {
                    $givenType = is_object($value) ? get_class($value) : gettype($value);
                    if (!$this->isValidType($type, $value)) {
                        throw new \InvalidArgumentException(
                            'The argument "' . $argumentName . '" was registered with type "' . $type . '", but is of type "' .
                            $givenType . '" in view helper "' . get_class($this) . '".',
                            1256475113
                        );
                    }
                }
            }
        }
    }

    /**
     * Check whether the defined type matches the value type
     *
     * @param string $type
     * @param mixed $value
     * @return boolean
     */
    protected function isValidType($type, $value)
    {
        if ($type === 'object') {
            if (!is_object($value)) {
                return false;
            }
        } elseif ($type === 'array' || substr($type, -2) === '[]') {
            if (!is_array($value) && !$value instanceof \ArrayAccess && !$value instanceof \Traversable && !empty($value)) {
                return false;
            } elseif (substr($type, -2) === '[]') {
                $firstElement = $this->getFirstElementOfNonEmpty($value);
                if ($firstElement === null) {
                    return true;
                }
                return $this->isValidType(substr($type, 0, -2), $firstElement);
            }
        } elseif ($type === 'string') {
            if (is_object($value) && !method_exists($value, '__toString')) {
                return false;
            }
        } elseif ($type === 'boolean' && !is_bool($value)) {
            return false;
        } elseif (class_exists($type) && $value !== null && !$value instanceof $type) {
            return false;
        } elseif (is_object($value) && !is_a($value, $type, true)) {
            return false;
        }
        return true;
    }

    /**
     * Return the first element of the given array, ArrayAccess or Traversable
     * that is not empty
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getFirstElementOfNonEmpty($value)
    {
        if (is_array($value)) {
            return reset($value);
        } elseif ($value instanceof \Traversable) {
            foreach ($value as $element) {
                return $element;
            }
        }
        return null;
    }

}
