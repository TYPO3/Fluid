<?php
namespace TYPO3\Fluid\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Controller which provides a web UI for generating ViewHelper XSD Definitons
 *
 */
class StandardController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * XSD Generator
	 * @var TYPO3\Fluid\Service\XsdGenerator
	 */
	protected $xsdGenerator;

	/**
	 * Inject XSD Generator
	 *
	 * @param \TYPO3\Fluid\Service\XsdGenerator $xsdGenerator XSD Generator
	 * @return void
	 */
	public function injectXsdGenerator(\TYPO3\Fluid\Service\XsdGenerator $xsdGenerator) {
		$this->xsdGenerator = $xsdGenerator;
	}

	/**
	 * Inject a TemplateView
	 *
	 * @param \TYPO3\Fluid\View\TemplateView $view The Fluid View Instance
	 * @return void
	 */
	public function injectView(\TYPO3\Fluid\View\TemplateView $view) {
		$this->view = $view;
	}

	/**
	 * Index action
	 *
	 * @return string HTML string
	 */
	public function indexAction() {
		return $this->view->render();
	}

	/**
	 * Generate the XSD file.
	 *
	 * @param string $baseNamespace
	 * @param string $namespacePrefix
	 * @return string HTML string
	 * @todo Still has to be finished
	 */
	public function generateXsdAction($baseNamespace, $namespacePrefix) {
		$xsdFileContents = $this->xsdGenerator->generateXSD($baseNamespace);

		$path = 'Resources/Fluid/XSD/';
		if (!is_dir(FLOW3_PATH_WEB . $path)) {
			\TYPO3\FLOW3\Utility\Files::createDirectoryRecursively(FLOW3_PATH_WEB . $path);
		}

		$filename = $path . str_replace('\\', '_', $baseNamespace) . '.xsd';

		$fp = fopen(FLOW3_PATH_WEB . $filename, 'w');
		fputs($fp, $xsdFileContents);
		fclose($fp);

		return $this->view->assign('xsdPath', $filename)
		                  ->assign('namespaceUri', 'http://typo3.org/ns/fluid/' . str_replace('\\', '/', $baseNamespace))
		                  ->assign('namespacePrefix', $namespacePrefix)
		                  ->assign('view', $this->view)
		                  ->render();
	}
}
?>