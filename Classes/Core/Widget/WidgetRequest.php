<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Widget;

/*
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
 * Represents a widget request.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class WidgetRequest extends \F3\FLOW3\MVC\Web\Request {

	/**
	 * @var F3\Fluid\Core\Widget\WidgetContext
	 */
	protected $widgetContext;

	/**
	 * @return F3\Fluid\Core\Widget\WidgetContext
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getWidgetContext() {
		return $this->widgetContext;
	}

	/**
	 * @param F3\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 */
	public function setWidgetContext(\F3\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$this->widgetContext = $widgetContext;
		$this->setControllerObjectName($widgetContext->getControllerObjectName());
	}
}
?>