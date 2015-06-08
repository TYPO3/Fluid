<?php
namespace NamelessCoder\Fluid\Tests\Example;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use NamelessCoder\Fluid\Core\Variables\StandardVariableProvider;
use NamelessCoder\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Class CustomVariableProvider
 *
 * Custom VariableProvider which does a bit of
 * state manipulation on variables. Used by
 * example_variableprovider.php.
 */
class CustomVariableProvider extends StandardVariableProvider implements VariableProviderInterface {

	/**
	 * @var integer
	 */
	protected $incrementer = 0;

	/**
	 * @param string $identifier
	 * @return mixed
	 */
	public function get($identifier) {
		if ($identifier === 'random') {
			return 'random' . sha1(rand(0, 999999999999));
		} elseif ($identifier === 'incrementer') {
			return ++ $this->incrementer;
		} else {
			return parent::get($identifier);
		}
	}

	/**
	 * @param string $identifier
	 * @return boolean
	 */
	public function exists($identifier) {
		return ($identifier === 'incrementer' || $identifier === 'random' || parent::exists($identifier));
	}

}
