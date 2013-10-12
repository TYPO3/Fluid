<?php
namespace TYPO3\Fluid\Core\Compiler;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Object\Configuration\Configuration;
use TYPO3\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 */
abstract class AbstractCompiledTemplate implements ParsedTemplateInterface {

	/**
	 * @var array<\SplObjectStorage>
	 */
	protected $viewHelpersByPositionAndContext = array();

	// These tokens are replaced by the Backporter for implementing different behavior in TYPO3 v4
	// TOKEN-1

	/**
	 * Public such that it is callable from within closures
	 *
	 * @param integer $uniqueCounter
	 * @param RenderingContextInterface $renderingContext
	 * @param string $viewHelperName
	 * @return AbstractViewHelper
	 * @Flow\Internal
	 */
	public function getViewHelper($uniqueCounter, RenderingContextInterface $renderingContext, $viewHelperName) {
		if (Bootstrap::$staticObjectManager->getScope($viewHelperName) === Configuration::SCOPE_SINGLETON) {
			// if ViewHelper is Singleton, do NOT instantiate with NEW, but re-use it.
			$viewHelper = Bootstrap::$staticObjectManager->get($viewHelperName);
			$viewHelper->resetState();
			return $viewHelper;
		}
		if (isset($this->viewHelpersByPositionAndContext[$uniqueCounter])) {
			/** @var $viewHelpers \SplObjectStorage */
			$viewHelpers = $this->viewHelpersByPositionAndContext[$uniqueCounter];
			if ($viewHelpers->contains($renderingContext)) {
				$viewHelper = $viewHelpers->offsetGet($renderingContext);
				$viewHelper->resetState();
				return $viewHelper;
			} else {
				$viewHelperInstance = new $viewHelperName;
				$viewHelpers->attach($renderingContext, $viewHelperInstance);
				return $viewHelperInstance;
			}
		} else {
			$viewHelperInstance = new $viewHelperName;
			$viewHelpers = new \SplObjectStorage();
			$viewHelpers->attach($renderingContext, $viewHelperInstance);
			$this->viewHelpersByPositionAndContext[$uniqueCounter] = $viewHelpers;
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
