<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MutableTestViewHelper extends AbstractViewHelper
{
    public function prepareArguments()
    {
        // Override to avoid the static cache of registered ViewHelper arguments; will always return
        // only those arguments that are registered in this particular instance.
        return $this->argumentDefinitions;
    }

    public function setEscapeChildren($escapeChildren): void
    {
        // Public method to force escape-children behavior which is normally only possible to set in class properties
        $this->escapeChildren = $escapeChildren;
    }

    public function setEscapeOutput($escapeOutput): void
    {
        // Public method to force escape-content behavior which is normally only possible to set in class properties
        $this->escapeOutput = $escapeOutput;
    }

    public function registerArgument($name, $type, $description, $required = false, $defaultValue = null, $escaped = null)
    {
        return parent::registerArgument($name, $type, $description, $required, $defaultValue, $escaped);
    }

    public function withContentArgument($escaped = null): self
    {
        // TODO: set escaping behavior if $escaped !== null
        $clone = clone $this;
        $clone->registerArgument('content', 'string', 'Content argument', false, null, $escaped);
        return $clone;
    }

    public function withOutputArgument($escaped = null): self
    {
        // TODO: set escaping behavior if $escaped !== null
        $clone = clone $this;
        $clone->registerArgument('output', 'string', 'Content argument', true, null, $escaped);
        return $clone;
    }

    public function withEscapeChildren($escapeChildren): self
    {
        $clone = clone $this;
        $clone->setEscapeChildren($escapeChildren);
        return $clone;
    }

    public function withEscapeOutput($escapeOutput): self
    {
        $clone = clone $this;
        $clone->setEscapeOutput($escapeOutput);
        return $clone;
    }

    public function resolveContentArgumentName()
    {
        return 'content';
    }

    public function render()
    {
        $argumentDefinitions = $this->prepareArguments();
        if (isset($argumentDefinitions['content'])) {
            return $this->arguments['content'] ?: $this->renderChildren();
        }
        if (isset($argumentDefinitions['output'])) {
            return $this->arguments['output'];
        }
        return $this->renderChildren();
    }
}