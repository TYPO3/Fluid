<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Controller;

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
 * Controller which provides a web UI for generating ViewHelper XSD Definitons
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StandardController extends \F3\FLOW3\MVC\Controller\ActionController {

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
		if (!is_dir(FLOW3_PATH_WEB . $path)) {
			\F3\FLOW3\Utility\Files::createDirectoryRecursively(FLOW3_PATH_WEB . $path);
		}

		$filename = $path . str_replace('\\', '_', $baseNamespace) . '.xsd';

		$fp = fopen(FLOW3_PATH_WEB . $filename, 'w');
		fputs($fp, $xsdFileContents);
		fclose($fp);

		return $this->view->assign('xsdPath', $filename)
		                  ->assign('namespaceURI', 'http://typo3.org/ns/fluid/' . str_replace('\\', '/', $baseNamespace))
		                  ->assign('namespacePrefix', $namespacePrefix)
		                  ->render();
	}
}
?>