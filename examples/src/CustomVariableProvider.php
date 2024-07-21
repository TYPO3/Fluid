<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\FluidExamples;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Class CustomVariableProvider
 *
 * Custom VariableProvider which does a bit of
 * state manipulation on variables. Used by
 * example_variableprovider.php.
 */
class CustomVariableProvider extends StandardVariableProvider implements VariableProviderInterface
{
    protected int $incrementer = 0;

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     * If the second variable is passed, it is expected to contain
     * extraction method names (constants from StandardVariableProvider)
     * which indicate how each value is extracted.
     */
    public function getByPath(string $path): mixed
    {
        if ($path === 'random') {
            return 'random' . hash('xxh3', (string)rand(0, 999999999));
        }
        if ($path === 'incrementer') {
            return ++$this->incrementer;
        }
        return parent::getByPath($path);
    }

    public function exists(string $identifier): bool
    {
        return $identifier === 'incrementer' || $identifier === 'random' || parent::exists($identifier);
    }
}
