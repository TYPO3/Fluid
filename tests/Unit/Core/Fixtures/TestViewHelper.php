<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class TestViewHelper
 */
class TestViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('param1', 'integer', 'P1 Stuff', true);
        $this->registerArgument('param2', 'array', 'P2 Stuff', true);
        $this->registerArgument('param3', 'string', 'P3 Stuff', false, 'default');
    }

    /**
     * My comments.
     *
     * @return string
     */
    public function render()
    {
        return $this->arguments['param1'];
    }

    /**
     * Handle any additional comments by ignoring them
     *
     * @param array $arguments
     */
    public function handleAdditionalArguments(array $arguments)
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
