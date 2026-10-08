<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Add a value to a given array variable. If the variable is null or empty,
 * it will be initialized as an empty array.
 *
 *  Examples
 *  ========
 *
 *  Add a value to the end of an array
 *  ----------------------------------
 *
 *  ::
 *
 *      <f:variable name="tags"/>
 *      <f:for each="{newsItem.tags}" as="tag">
 *          <f:push name="tags" value="{tag.title}" />
 *      </f:for>
 *
 *  Add a value with a key to an array
 *  ----------------------------------
 *
 *  If the key already exists, the value will be overridden.
 *
 *  ::
 *
 *      <f:push name="tags" key="some-key" value="some-value" />
 */
final class PushViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'value',
            'mixed',
            'Value to push to specified array variable. If not in arguments then taken from tag content.',
        );
        $this->registerArgument(
            'name',
            'string',
            'Name of variable to extend.',
            true,
        );
        $this->registerArgument(
            'key',
            'string',
            'Key that should be used in the array',
        );
    }

    public function render(): void
    {
        $value = $this->arguments['value'] ?? $this->renderChildren();

        $variable = $this->renderingContext->getVariableProvider()->get($this->arguments['name']);
        if (! \is_array($variable)) {
            $variable = [];
        }
        if ($this->arguments['key']) {
            $variable[$this->arguments['key']] = $value;
        } else {
            $variable[] = $value;
        }

        $this->renderingContext->getVariableProvider()->add($this->arguments['name'], $variable);
    }
}
