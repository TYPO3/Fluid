<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid;

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
 * @version $Id:$
 */
/**
 * An test view helper - needed to test "registerArgument".
 *
 * @package Fluid
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class TestViewHelper extends \F3\Fluid\Core\AbstractViewHelper {
	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function __construct($name, $type, $description, $isOptional) {
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->isOptional = $isOptional;
	}
	
	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument($this->name, $this->type, $this->description, $this->isOptional);
	}
	
	public function render() {
		
	}

}


?>
