<?php
namespace TYPO3\Fluid\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for Fluid documentation rendering
 *
 * @Flow\Scope("singleton")
 */
class DocumentationCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Fluid\Service\XsdGenerator
	 */
	protected $xsdGenerator;

	/**
	 * Generate Fluid ViewHelper XSD Schema
	 *
	 * Generates Schema documentation (XSD) for your ViewHelpers, preparing the
	 * file to be placed online and used by any XSD-aware editor.
	 * After creating the XSD file, reference it in your IDE and import the namespace
	 * in your Fluid template by adding the xmlns:* attribute(s):
	 * <html xmlns="http://www.w3.org/1999/xhtml" xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers" ...>
	 *
	 * @param string $phpNamespace Namespace of the Fluid ViewHelpers without leading backslash (for example 'TYPO3\Fluid\ViewHelpers'). NOTE: Quote and/or escape this argument as needed to avoid backslashes from being interpreted!
	 * @param string $xsdNamespace Unique target namespace used in the XSD schema (for example "http://yourdomain.org/ns/viewhelpers"). Defaults to "http://typo3.org/ns/<php namespace>".
	 * @param string $targetFile File path and name of the generated XSD schema. If not specified the schema will be output to standard output.
	 * @return void
	 */
	public function generateXsdCommand($phpNamespace, $xsdNamespace = NULL, $targetFile = NULL) {
		if ($xsdNamespace === NULL) {
			$xsdNamespace = sprintf('http://typo3.org/ns/%s', str_replace('\\', '/', $phpNamespace));
		}
		try {
			$xsdSchema = $this->xsdGenerator->generateXsd($phpNamespace, $xsdNamespace);
		} catch (\TYPO3\Fluid\Service\Exception $exception) {
			$this->outputLine('An error occured while trying to generate the XSD schema:');
			$this->outputLine('%s', array($exception->getMessage()));
			$this->quit(1);
		}
		if ($targetFile === NULL) {
			$this->output($xsdSchema);
		} else {
			file_put_contents($targetFile, $xsdSchema);
		}
	}
}

?>