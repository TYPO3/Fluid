<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 */

/**
 *
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
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
