<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * This view helper is an abstract ViewHelper which implements an if/else condition.
 *
 * = Usage =
 *
 * To create a custom Condition ViewHelper, you need to subclass this class, and
 * implement your own render() method. Inside there, you should call $this->renderThenChild()
 * if the condition evaluated to TRUE, and $this->renderElseChild() if the condition evaluated
 * to FALSE.
 *
 * Every Condition ViewHelper has a "then" and "else" argument, so it can be used like:
 * <[aConditionViewHelperName] .... then="condition true" else="condition false" />,
 * or as well use the "then" and "else" child nodes.
 *
 * @see TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper for a more detailed explanation and a simple usage example.
 * Make sure to NOT OVERRIDE the constructor.
 *
 * @api
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('then', 'mixed', 'Value to be returned if the condition if met.');
        $this->registerArgument('else', 'mixed', 'Value to be returned if the condition if not met.');
    }

    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     * Method which only gets called if the template is not compiled. For static calling,
     * the then/else nodes are converted to closures and condition evaluation closures.
     *
     * @return mixed
     * @api
     */
    public function render()
    {
        if (static::verdict($this->arguments, $this->renderingContext)) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

    /**
     * Static method which can be overridden by subclasses. If a subclass
     * requires a different (or faster) decision then this method is the one
     * to override and implement.
     *
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @return bool
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return static::evaluateCondition($arguments);
    }

    /**
     * Static method which can be overridden by subclasses. If a subclass
     * requires a different (or faster) decision then this method is the one
     * to override and implement.
     *
     * Note: method signature does not type-hint that an array is desired,
     * and as such, *appears* to accept any input type. There is no type hint
     * here for legacy reasons - the signature is kept compatible with third
     * party packages which depending on PHP version would error out if this
     * signature was not compatible with that of existing and in-production
     * subclasses that will be using this base class in the future. Let this
     * be a warning if someone considers changing this method signature!
     *
     * @deprecated Deprecated in favor of ClassName::verdict($arguments, renderingContext), will no longer be called in 3.0
     * @param array|null $arguments
     * @return bool
     * @api
     */
    protected static function evaluateCondition(array $arguments = null)
    {
        return isset($arguments['condition']) && (bool)($arguments['condition']);
    }

    /**
     * Returns value of "then" attribute.
     * If then attribute is not set, iterates through child nodes and renders ThenViewHelper.
     * If then attribute is not set and no ThenViewHelper and no ElseViewHelper is found, all child nodes are rendered
     *
     * @return mixed rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
     * @api
     */
    protected function renderThenChild()
    {
        if ($this->hasArgument('then')) {
            return $this->arguments['then'];
        }

        $elseViewHelperEncountered = false;
        foreach ($this->getChildren() as $childNode) {
            if ($childNode instanceof ThenViewHelper) {
                $data = $childNode->execute($this->renderingContext);
                return $data;
            }
            if ($childNode instanceof ElseViewHelper) {
                $elseViewHelperEncountered = true;
            }
        }

        if ($elseViewHelperEncountered) {
            return '';
        } else {
            return $this->renderChildren();
        }
    }

    /**
     * Returns value of "else" attribute.
     * If else attribute is not set, iterates through child nodes and renders ElseViewHelper.
     * If else attribute is not set and no ElseViewHelper is found, an empty string will be returned.
     *
     * @return string rendered ElseViewHelper or an empty string if no ThenViewHelper was found
     * @api
     */
    protected function renderElseChild()
    {

        if ($this->hasArgument('else')) {
            return $this->arguments['else'];
        }

        /** @var ComponentInterface|null $elseNode */
        $elseNode = null;
        foreach ($this->getChildren() as $childNode) {
            if ($childNode instanceof ElseViewHelper) {
                $arguments = $childNode->getParsedArguments();
                if (isset($arguments['if'])) {
                    $condition = $arguments['if'];
                    if ($condition instanceof ComponentInterface) {
                        $condition = $condition->execute($this->renderingContext);
                    }
                    if ((bool)$condition === true) {
                        return $childNode->execute($this->renderingContext);
                    }
                } else {
                    $elseNode = $childNode;
                }
            }
        }

        return $elseNode instanceof ComponentInterface ? $elseNode->execute($this->renderingContext) : '';
    }
}
