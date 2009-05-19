<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser;

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
 * This interface is returned by \F3\Fluid\Core\Parser\TemplateParser->parse() method and is a parsed template
 *
 * @package Fluid
 * @subpackage Core
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @internal
 */
interface ParsedTemplateInterface {

	/**
	 * Render the parsed template with rendering context
	 *
	 * @param F3\Fluid\Core\RenderingContext $renderingContext The rendering context to use
	 * @return Rendered string
	 * @internal
	 */
	public function render(\F3\Fluid\Core\RenderingContext $renderingContext);

	/**
	 * Returns a variable container used in the PostParse Facet.
	 *
	 * @return \F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 * @internal
	 */
	// TODO
	public function getVariableContainer(); // rename to getPostParseVariableContainer -- @internal definitely
}

?>