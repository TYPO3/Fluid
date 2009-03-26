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
 * @package
 * @subpackage
 * @version $Id:$
 */
class ParameterReflectionService {

	/**
	 * @var \F3\FLOW3\Reflection\Service
	 */
	protected $reflectionService;

	public function injectReflectionService(\F3\FLOW3\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	public function getMethodParameters($className, $methodName) {

		$methodParameters = $this->reflectionService->getMethodParameters($className, $methodName);

		$methodTags = $this->reflectionService->getMethodTagsValues($className, $methodName);

		$paramAnnotations = array();
		if (isset($methodTags['param'])) {
			$paramAnnotations = $methodTags['param'];
		}

		$i = 0;
		if (!count($methodParameters)) return array();

		$output = array();
		foreach ($methodParameters as $parameterName => $parameterInfo) {
			$dataType = 'Text';

			if (isset($parameterInfo['type'])) {
				$dataType = $parameterInfo['type'];
			} elseif ($parameterInfo['array']) {
				$dataType = 'array';
			}

			$description = '';
			if (isset($paramAnnotations[$i])) {
				$explodedAnnotation = explode(' ', $paramAnnotations[$i]);
				array_shift($explodedAnnotation);
				array_shift($explodedAnnotation);
				$description = implode(' ', $explodedAnnotation);
			}
			$output[] = array(
				'name' => $parameterName,
				'dataType'=> $dataType,
				'description' => $description,
				'required' => ($parameterInfo['optional'] === FALSE)
			);

			$i++;
		}

		return $output;
	}
}


?>