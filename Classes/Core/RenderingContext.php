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
 * @version $Id: RuntimeException.php 2246 2009-05-18 17:56:39Z sebastian $
 */

/**
 * A Runtime Exception
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id: RuntimeException.php 2246 2009-05-18 17:56:39Z sebastian $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @internal
 * @scope prototype
 */
class RenderingContext {
	/**
	 * Template Variable Container. Contains all variables available through object accessors in the template
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;
	
	
	protected $objectFactory;
	public function injectObjectFactory(\F3\FLOW3\Object\FactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}
	
	public function getObjectFactory() {
		return $this->objectFactory;
	}
	
	public function setTemplateVariableContainer(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer) {
		$this->templateVariableContainer = $templateVariableContainer;
	}
	
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}
}
?>
