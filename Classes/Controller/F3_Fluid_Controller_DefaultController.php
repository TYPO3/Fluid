<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Controller;

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
 * @subpackage Controller
 * @version $Id:$
 */
/**
 * Controller which provides a web UI for generating ViewHelper XSD Definitons
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultController extends \F3\FLOW3\MVC\Controller\ActionController {
	
	/**
	 * XSD Generator
	 * @var F3\Fluid\Service\XSDGenerator
	 */
	protected $xsdGenerator;

	/**
	 * Inject XSD Generator
	 * 
	 * @param \F3\Fluid\Service\XSDGenerator $xsdGenerator XSD Generator
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectXSDGenerator(\F3\Fluid\Service\XSDGenerator $xsdGenerator) {
		$this->xsdGenerator = $xsdGenerator;
	}
	
	/**
	 * Inject a TemplateView
	 * 
	 * @param \F3\Fluid\View\TemplateView $view The Beer3 View  Instance
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectView(\F3\Fluid\View\TemplateView $view) {
		$this->view = $view;
	}
	
	/**
	 * Initialize the view correctly.
	 * 
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeView() {
		$this->view->setRequest($this->request);
	}
	
	/**
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function indexAction() {
		return $this->xsdGenerator->generateXSD('F3\Fluid\ViewHelpers');
	}
}
?>