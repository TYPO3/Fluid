<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TestViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('param1', 'integer', 'P1 Stuff', true);
        $this->registerArgument('param2', 'array', 'P2 Stuff', true);
        $this->registerArgument('param3', 'string', 'P3 Stuff', false, 'default');
    }

    public function render(): string
    {
        return $this->arguments['param1'];
    }

    /**
     * Handle any additional comments by ignoring them
     */
    public function handleAdditionalArguments(array $arguments): void
    {
        $filtered = [];
        foreach ($arguments as $name => $value) {
            if (isset($this->argumentDefinitions[$name])) {
                $filtered[$name] = $value;
            }
        }
        parent::handleAdditionalArguments($filtered);
    }
}
