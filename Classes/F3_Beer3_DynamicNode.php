<?php
declare(ENCODING = 'utf-8');
namespace F3::Beer3;

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
 * @package Beer3
 * @version $Id:$
 */
/**
 * Dynamic node
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class DynamicNode extends F3::Beer3::AbstractNode {
	
	/**
	 * Namespace of view helper
	 * @var string
	 */
	protected $viewHelperNamespace;

	/**
	 * Name of view helper
	 * @var string
	 */
	protected $viewHelperName;
	
	/**
	 * Arguments of view helper - References to ArgumentRootNodes.
	 * @var array
	 */
	protected $arguments = array();
	
	/**
	 * @var F3::Beer3::DynamicNodeHelper
	 */
	protected $dynamicNodeHelper;
	
	/**
	 * @var F3::FLOW3::Component::FactoryInterface
	 */
	protected $componentFactory;
	
	/**
	 * Constructor.
	 * 
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($viewHelperNamespace, $viewHelperName, $objectToCall, $arguments) {
		$this->viewHelperNamespace = $viewHelperNamespace;
		$this->viewHelperName = $viewHelperName;
		$this->arguments = $arguments;
		$this->objectToCall = $objectToCall;
	}

	/**
	 * Get the namespace name of the view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperNamespace() {
		return $this->viewHelperNamespace;
	}
	
	/**
	 * Get name of view helper
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperName() {
		return $this->viewHelperName;
	}
	
	public function render(F3::Beer3::Context $context) {

	}
}


?>