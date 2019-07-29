<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\ComponentInterface;
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
 * @see \TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper for a more detailed explanation and a simple usage example.
 * Make sure to NOT OVERRIDE the constructor.
 */
abstract class AbstractConditionViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @return void
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
     */
    public function render()
    {
        if ($this->condition()) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

    /**
     * Override this method for a custom decision whether or not to render the ViewHelper
     *
     * @return bool
     */
    protected function condition(): bool
    {
        return true;
    }

    /**
     * Returns value of "then" attribute.
     * If then attribute is not set, iterates through child nodes and renders ThenViewHelper.
     * If then attribute is not set and no ThenViewHelper and no ElseViewHelper is found, all child nodes are rendered
     *
     * @return mixed rendered ThenViewHelper or contents of <f:if> if no ThenViewHelper was found
     */
    protected function renderThenChild()
    {
        if ($this->hasArgument('then')) {
            return $this->arguments['then'];
        }
        $elseViewHelperEncountered = false;
        foreach ($this->getChildren() as $childNode) {
            if ($childNode instanceof ThenViewHelper) {
                $data = $childNode->evaluate($this->renderingContext);
                return $data;
            }
            if ($childNode instanceof ElseViewHelper) {
                $elseViewHelperEncountered = true;
            }
        }

        if ($elseViewHelperEncountered) {
            return null;
        }

        return $this->evaluateChildren($this->renderingContext);
    }

    /**
     * Returns value of "else" attribute.
     * If else attribute is not set, iterates through child nodes and renders ElseViewHelper.
     * If else attribute is not set and no ElseViewHelper is found, an empty string will be returned.
     *
     * @return string rendered ElseViewHelper or an empty string if no ThenViewHelper was found
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
                $conditionArgument = $childNode->getArguments()->setRenderingContext($this->renderingContext)['if'];
                if ($conditionArgument) {
                    return $childNode->evaluate($this->renderingContext);
                }
            }
        }

        return null;
    }
}
