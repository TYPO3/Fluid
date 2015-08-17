<?php
namespace TYPO3Fluid\Fluid\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Interface of a view
 *
 * @api
 */
interface ViewInterface {

	/**
	 * Add a variable to the view data collection.
	 * Can be chained, so $this->view->assign(..., ...)->assign(..., ...); is possible
	 *
	 * @param string $key Key of variable
	 * @param mixed $value Value of object
	 * @return ViewInterface an instance of $this, to enable chaining
	 * @api
	 */
	public function assign($key, $value);

	/**
	 * Add multiple variables to the view data collection
	 *
	 * @param array $values array in the format array(key1 => value1, key2 => value2)
	 * @return \TYPO3Fluid\Flow\Mvc\View\ViewInterface an instance of $this, to enable chaining
	 * @api
	 */
	public function assignMultiple(array $values);

	/**
	 * Renders the view
	 *
	 * @return string The rendered view
	 * @api
	 */
	public function render();

}
