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
 * Controller for documentation rendering
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
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
	 * @return string
	 */
	public function generateAction($sourceNamespace = 'F3\Fluid\ViewHelpers', $targetFile = '../Packages/Global/Fluid/Documentation/Manual/DocBook/en/ViewHelperLibrary.xml') {
		$output = $this->docbookGenerator->generateDocbook($sourceNamespace);
		file_put_contents($targetFile, $output);
		return 'Documentation generated.';
	}
}

?>