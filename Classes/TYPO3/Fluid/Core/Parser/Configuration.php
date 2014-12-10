<?php
namespace TYPO3\Fluid\Core\Parser;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The parser configuration. Contains all configuration needed to configure
 * the building of a SyntaxTree.
 */
class Configuration {

	/**
	 * Generic interceptors registered with the configuration.
	 *
	 * @var array<\SplObjectStorage>
	 */
	protected $interceptors = array();

	/**
	 * Adds an interceptor to apply to values coming from object accessors.
	 *
	 * @param InterceptorInterface $interceptor
	 * @return void
	 */
	public function addInterceptor(InterceptorInterface $interceptor) {
		foreach ($interceptor->getInterceptionPoints() as $interceptionPoint) {
			if (!isset($this->interceptors[$interceptionPoint])) {
				$this->interceptors[$interceptionPoint] = new \SplObjectStorage();
			}
			/** @var $interceptors \SplObjectStorage */
			$interceptors = $this->interceptors[$interceptionPoint];
			if (!$interceptors->contains($interceptor)) {
				$interceptors->attach($interceptor);
			}
		}
	}

	/**
	 * Returns all interceptors for a given Interception Point.
	 *
	 * @param integer $interceptionPoint one of the \TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants,
	 * @return \SplObjectStorage<\TYPO3\Fluid\Core\Parser\InterceptorInterface>
	 */
	public function getInterceptors($interceptionPoint) {
		if (isset($this->interceptors[$interceptionPoint]) && $this->interceptors[$interceptionPoint] instanceof \SplObjectStorage) {
			return $this->interceptors[$interceptionPoint];
		}
		return new \SplObjectStorage();
	}

}
