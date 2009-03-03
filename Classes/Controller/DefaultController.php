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
 * @version $Id$
 */

/**
 * Controller which provides a web UI for generating ViewHelper XSD Definitons
 *
 * @package Fluid
 * @subpackage Controller
 * @version $Id$
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
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectXSDGenerator(\F3\Fluid\Service\XSDGenerator $xsdGenerator) {
		$this->xsdGenerator = $xsdGenerator;
	}

	/**
	 * Inject a TemplateView
	 *
	 * @param \F3\Fluid\View\TemplateView $view The Fluid View Instance
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
	 * Index action
	 *
	 * @return string HTML string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function indexAction() {
		return $this->view->render();
	}

	/**
	 * Generate the XSD file.
	 *
	 * @param $baseNamespace string
	 * @param $namespacePrefix string
	 * @return string HTML string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @todo Still has to be finished
	 */
	public function generateXSDAction($baseNamespace, $namespacePrefix) {
		$xsdFileContents = $this->xsdGenerator->generateXSD($baseNamespace);

		$path = 'Resources/Fluid/XSD/';
		if (!is_dir(FLOW3_PATH_PUBLIC . $path)) {
			\F3\FLOW3\Utility\Files::createDirectoryRecursively(FLOW3_PATH_PUBLIC . $path);
		}

		$filename = $path . str_replace('\\', '_', $baseNamespace) . '.xsd';

		$fp = fopen(FLOW3_PATH_PUBLIC . $filename, 'w');
		fputs($fp, $xsdFileContents);
		fclose($fp);

		return $this->view->addVariable('xsdPath', $filename)
		                  ->addVariable('namespaceURI', 'http://typo3.org/ns/fluid/' . str_replace('\\', '/', $baseNamespace))
		                  ->addVariable('namespacePrefix', $namespacePrefix)
		                  ->render();
	}
}
?>