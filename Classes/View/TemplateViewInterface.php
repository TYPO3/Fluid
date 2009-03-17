<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View;

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
 * @subpackage View
 * @version $Id$
 */

/**
 * Interface of Fluids Template view
 *
 * @package Fluid
 * @subpackage View
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
interface TemplateViewInterface extends \F3\FLOW3\MVC\View\ViewInterface {

	/**
	 * Inject the template parser
	 *
	 * @param \F3\Fluid\Core\TemplateParser $templateParser The template parser
	 * @return void
	 */
	public function injectTemplateParser(\F3\Fluid\Core\TemplateParser $templateParser);

	/**
	 * Sets the path and name of of the template file. Effectively overrides the
	 * dynamic resolving of a template file.
	 *
	 * @param string $templatePathAndFilename Template file path
	 * @return void
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename);

	/**
	 * Sets the path and name of the layout file. Overrides the dynamic resolving of the layout file.
	 *
	 * @param string $layoutPathAndFilename Path and filename of the layout file
	 * @return void
	 */
	public function setLayoutPathAndFilename($layoutPathAndFilename);

	/**
	 * Renders a given section.
	 *
	 * @param string $sectionName Name of section to render
	 * @return rendered template for the section
	 */
	public function renderSection($sectionName);

	/**
	 * Render a template with a given layout.
	 *
	 * @param string $layoutName Name of layout
	 * @return string rendered HTML
	 */
	public function renderWithLayout($layoutName);

	/**
	 * Add a variable to the context.
	 * Can be chained, so $template->addVariable(..., ...)->addVariable(..., ...); is possible,
	 *
	 * @param string $key Key of variable
	 * @param object $value Value of object
	 * @return \F3\Fluid\View\TemplateViewInterface an instance of $this, to enable chaining.
	 */
	public function assign($key, $value);

	/**
	 * Return the current request
	 *
	 * @return \F3\FLOW3\MVC\Web\Request the current request
	 */
	public function getRequest();
}
?>
