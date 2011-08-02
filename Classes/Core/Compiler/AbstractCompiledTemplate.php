<?php
namespace TYPO3\Fluid\Core\Compiler;

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
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
abstract class AbstractCompiledTemplate implements \TYPO3\Fluid\Core\Parser\ParsedTemplateInterface {

	/**
	 * @var array
	 */
	protected $viewHelpersByPositionAndContext = array();

	// These tokens are replaced by the Backporter for implementing different behavior in TYPO3 v4
	// TOKEN-1

	/**
	 * Public such that it is callable from within closures
	 *
	 * @param integer $uniqueCounter
	 * @param \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
	 * @param string $viewHelperName
	 * @return \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper
	 * @internal
	 */
	public function getViewHelper($uniqueCounter, \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext, $viewHelperName) {
		if (\TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->getScope($viewHelperName) === \TYPO3\FLOW3\Object\Configuration\Configuration::SCOPE_SINGLETON) {
			// if ViewHelper is Singleton, do NOT instanciate with NEW, but re-use it.
			return \TYPO3\FLOW3\Core\Bootstrap::$staticObjectManager->get($viewHelperName);
		}
		if (isset($this->viewHelpersByPositionAndContext[$uniqueCounter])) {
			if ($this->viewHelpersByPositionAndContext[$uniqueCounter]->contains($renderingContext)) {
				return $this->viewHelpersByPositionAndContext[$uniqueCounter][$renderingContext];
			} else {
				$viewHelperInstance = new $viewHelperName;
				$this->viewHelpersByPositionAndContext[$uniqueCounter]->attach($renderingContext, $viewHelperInstance);
				return $viewHelperInstance;
			}
		} else {
			$this->viewHelpersByPositionAndContext[$uniqueCounter] = new \SplObjectStorage();
			$viewHelperInstance = new $viewHelperName;
			$this->viewHelpersByPositionAndContext[$uniqueCounter]->attach($renderingContext, $viewHelperInstance);
			return $viewHelperInstance;
		}
	}

	/**
	 * @return boolean
	 */
	public function isCompilable() {
		return FALSE;
	}

	/**
	 * @return boolean
	 */
	public function isCompiled() {
		return TRUE;
	}

	// TOKEN-2

}
?>