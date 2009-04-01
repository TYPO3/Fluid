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
 * @package
 * @subpackage
 * @version $Id:$
 */

class DocumentationController extends \F3\FLOW3\MVC\Controller\ActionController {
	/**
	 * Defines the supported request types of this controller
	 *
	 * @var array
	 */
	protected $supportedRequestTypes = array('F3\FLOW3\MVC\CLI\Request');

	/**
	 * @var F3\Fluid\Service\DocbookGenerator
	 * @inject
	 */
	protected $docbookGenerator;
	/*
	 * @param string $sourceNamespace
	 * @param string $targetFile
	 */
	public function generateAction($sourceNamespace, $targetFile) {
		$output = $this->docbookGenerator->generateDocbook($sourceNamespace);
		file_put_contents($targetFile, $output);
		return 'Documentation generated.';
	}
}

?>