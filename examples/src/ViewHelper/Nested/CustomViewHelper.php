<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\FluidExamples\ViewHelper\Nested;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CustomViewHelper
 *
 * This ViewHelper gets loaded by the custom
 * ViewHelperResolver and has a single argument
 * which in this default state is a `string`
 * argument.
 *
 * The ViewHelperResolver will then change this to
 * be an `array` argument type, gives it a default
 * value and makes it optional before delivering
 * the arguments definitions to Fluid.
 */
class CustomViewHelper extends AbstractViewHelper
{
    protected ?bool $escapeChildren = false;

    protected bool $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('page', 'string', 'An arbitrary page identifier', true);
    }

    public function render(): string
    {
        return 'The following is a dump of the "page"' . PHP_EOL
            . 'Argument passed to CustomViewHelper:' . PHP_EOL
            . '---------------------------' . PHP_EOL
            . var_export($this->arguments['page'], true) . PHP_EOL
            . '---------------------------';
    }
}
