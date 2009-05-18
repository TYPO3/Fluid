<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */

/**
 * ViewHelperContext
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class ViewHelperContext extends \F3\Fluid\Core\VariableContainer {

	/**
	 * @var \F3\FLOW3\MVC\View\ViewInterface
	 */
	protected $view;

	/**
	 * @var array
	 */
	protected $viewHelperDefaults = array();

	/**
	 * @param \F3\FLOW3\MVC\View\ViewInterface $view
	 * @param array $viewHelperDefaults
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function __construct(\F3\FLOW3\MVC\View\ViewInterface $view = NULL, array $viewHelperDefaults = array()) {
		$this->view = $view;
		$this->viewHelperDefaults = $viewHelperDefaults;
	}

	/**
	 * @return \F3\FLOW3\MVC\View\ViewInterface
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getView() {
		return $this->view;
	}

	/**
	 * @param string $viewHelperClassName full class name of the view helper to fetch defaults for
	 * @return array
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getViewHelperDefaults($viewHelperClassName) {
		if (isset($this->viewHelperDefaults[$viewHelperClassName]) && is_array($this->viewHelperDefaults[$viewHelperClassName])) {
			return $this->viewHelperDefaults[$viewHelperClassName];
		}
		return array();
	}
}

?>