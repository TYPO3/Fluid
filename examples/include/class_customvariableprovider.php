<?php
namespace TYPO3\Fluid\Tests\Example;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3\Fluid\Core\Variables\VariableProviderInterface;

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
	 * @return array
	 */
	public function getAll() {
		return array(
			'incrementer' => ++ $this->incrementer,
			'random' => 'random' . sha1(rand(0, 99999999999999))
		);
	}

	/**
	 * @param string $identifier
	 * @return mixed
	 */
	public function get($identifier) {
		$all = $this->getAll();
		return $all[$identifier];
	}

}
