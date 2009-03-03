<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\ViewHelpers\Format;

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
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 * Formats a \DateTime object.
 *
 * Example:
 *
 * <f3:format.date target="{myDateTimeObject}" format="d m y" />
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class DateViewHelper extends \F3\Fluid\Core\AbstractViewHelper {

	/**
	 * Registers two arguments: "target" and "format". Both are mandatory.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeArguments() {
		$this->registerArgument('target', 'DateTime', 'Date / Time object to format', TRUE);
		$this->registerArgument('format', 'string', 'Format String which is taken to format the Date/Time', TRUE);
	}

	/**
	 * Renders a formatted date.
	 *
	 * @return string the formatted date string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function render() {
		$dateObject = $this->arguments['target'];
		if ($dateObject instanceof \DateTime) {
			return $dateObject->format($this->arguments['format']);
		}
		return '';
	}
}
?>