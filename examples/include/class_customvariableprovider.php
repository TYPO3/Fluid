<?php
namespace TYPO3Fluid\Fluid\Tests\Example;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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

    /**
     * @var integer
     */
    protected $incrementer = 0;

    /**
     * Get a variable by dotted path expression, retrieving the
     * variable from nested arrays/objects one segment at a time.
     * If the second variable is passed, it is expected to contain
     * extraction method names (constants from VariableExtractor)
     * which indicate how each value is extracted.
     *
     * @param string $path
     * @return mixed
     */
    public function getByPath($path, array $accessors = [])
    {
        if ($path === 'random') {
            return 'random' . sha1(rand(0, 999999999));
        } elseif ($path === 'incrementer') {
            return ++ $this->incrementer;
        } else {
            return parent::getByPath($path);
        }
    }

    /**
     * @param string $identifier
     * @return boolean
     */
    public function exists($identifier)
    {
        return ($identifier === 'incrementer' || $identifier === 'random' || parent::exists($identifier));
    }
}
