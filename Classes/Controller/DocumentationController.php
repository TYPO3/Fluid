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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Controller for documentation rendering
 *
 */
class DocumentationController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * Defines the supported request types of this controller
	 *
	 * @var array
	 */
	protected $supportedRequestTypes = array('TYPO3\FLOW3\MVC\CLI\Request');

	/**
	 * @var TYPO3\Fluid\Service\DocbookGenerator
	 * @FLOW3\Inject
	 */
	protected $docbookGenerator;

	/**
	 * @param string $sourceNamespace
	 * @param string $targetFile
	 * @return string
	 */
	public function generateAction($sourceNamespace = 'TYPO3\Fluid\ViewHelpers', $targetFile = 'Packages/Framework/Fluid/Documentation/Manual/DocBook/en/ViewHelperLibrary.xml') {
		$output = $this->docbookGenerator->generateDocbook($sourceNamespace);
		file_put_contents($targetFile, $output);
		return 'Documentation generated.';
	}
}

?>