<?php
namespace TYPO3\Fluid\Core\Compiler;

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
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 *
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
	 * @Flow\Internal
	 */
	public function getViewHelper($uniqueCounter, \TYPO3\Fluid\Core\Rendering\RenderingContextInterface $renderingContext, $viewHelperName) {
		if (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getScope($viewHelperName) === \TYPO3\Flow\Object\Configuration\Configuration::SCOPE_SINGLETON) {
			// if ViewHelper is Singleton, do NOT instanciate with NEW, but re-use it.
			$viewHelper = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get($viewHelperName);
			$viewHelper->resetState();
			return $viewHelper;
		}
		if (isset($this->viewHelpersByPositionAndContext[$uniqueCounter])) {
			if ($this->viewHelpersByPositionAndContext[$uniqueCounter]->contains($renderingContext)) {
				$viewHelper = $this->viewHelpersByPositionAndContext[$uniqueCounter][$renderingContext];
				$viewHelper->resetState();
				return $viewHelper;
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