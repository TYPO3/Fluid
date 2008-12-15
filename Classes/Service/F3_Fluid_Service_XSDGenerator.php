<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Service;

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
 * @subpackage Service
 * @version $Id:$
 */
/**
 * [Enter description here]
 *
 * @package Fluid
 * @subpackage Service
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class XSDGenerator {
/**
	 * @var \F3\FLOW3\Reflection\Service
	 */
	protected $reflectionService;

	/**
	 * Inject a reflection service.
	 * 
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectReflectionService(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	public function generateXSD($packageName) {
		
	}
	
	/* TODO */
	
	/**
	 * Default action for this controller
	 *
	 * @return string The rendered view
	 * @author sebastian
	 */
	public function indexAction() {
		// TODO INPUT PARAMETERS _ LIMIT TO CERTAIN PACKAGE!
		
		$this->xmlHeader();
		
		$classNames = $this->reflectionService->getClassNamesByTag('viewhelper');
		foreach ($classNames as $className) {
			if (preg_match('/([^:]*)ViewHelper$/', $className, $match) > 0) {
				$viewHelperSubNamespace = $match[1];
				
				$methodNames = $this->reflectionService->getClassMethodNames($className);
				foreach ($methodNames as $methodName) {
					if (preg_match('/^(.*)Method$/', $methodName, $match) > 0) {
						$lastTagPart = $match[1];
						
						$tagName = $lastTagPart;
						if ($viewHelperSubNamespace != 'Default') {
							$tagName = \F3\PHP6\Functions\strtolower($viewHelperSubNamespace) . '.' . $tagName;
						}
						
						$this->outputElement($tagName, $className, $methodName);
					}
				}
			}
		}
		$this->out .= '</xsd:schema>';
		
		return $this->out;
		return $this->view->render();
	}
	
	protected function xmlHeader() {
		$this->out = '<?xml version="1.0" encoding="UTF-8"?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	targetNamespace="http://typo3.org/ns/f3">';
	}
	
	protected function outputElement($tagName, $className, $methodName) {
		$this->out .= '<xsd:element name="'.$tagName.'">';
		
		$reflectionMethod = new \ReflectionMethod($className, $methodName);
		$docCommentParser = new \F3\FLOW3\Reflection\DocCommentParser();
		$docCommentParser->parseDocComment($reflectionMethod->getDocComment());
		
		$description = $docCommentParser->getDescription();
		$this->out .= '<xsd:annotation><xsd:documentation>'.htmlentities($description).'</xsd:documentation></xsd:annotation>';
		
		$this->out .= '<xsd:complexType mixed="true">
			<xsd:sequence>
				<xsd:any minOccurs="0" maxOccurs="unbounded" />
			</xsd:sequence>';
		
		$tags = $this->reflectionService->getMethodTagsValues($className, $methodName);
		if ($tags['argument']) {
			foreach ($tags['argument'] as $argument) {
				preg_match('/(?<Name>[a-zA-Z0-9]*)\s+(?<Type>[a-zA-Z0-9]*)\s+(?<Description>.*)$/', $argument, $matches);
				
				// todo use="required"
				$this->out .= '<xsd:attribute name="'.$matches['Name'].'" type="xsd:string">';
				
				$this->out .= '<xsd:annotation><xsd:documentation>';
				$this->out .= htmlentities($matches['Description']);
				$this->out .= '</xsd:documentation></xsd:annotation>';
				
				$this->out .= '</xsd:attribute>';
			}
			
		}
		
		$this->out .= '</xsd:complexType>';
		
		$this->out .= '</xsd:element>';
		
	}
	
}
?>