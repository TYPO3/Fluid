<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser;

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
 * The parser configuration. Contains all configuration needed to configure
 * the building of a SyntaxTree.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Configuration {

	/**
	 * generic interceptors registered with the configuration.
	 * @var array<\SplObjectStorage>
	 */
	protected $interceptors;

	/**
	 * Set up the internals...
	 *
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct() {
		$this->interceptors = array();
	}

	/**
	 * Adds an interceptor to apply to values coming from object accessors.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addInterceptor(\F3\Fluid\Core\Parser\InterceptorInterface $interceptor) {
		foreach ($interceptor->getInterceptionPoints() as $interceptionPoint) {
			if (!isset($this->interceptors[$interceptionPoint])) {
				$this->interceptors[$interceptionPoint] = new \SplObjectStorage();
			}
			if (!$this->interceptors[$interceptionPoint]->contains($interceptor)) {
				$this->interceptors[$interceptionPoint]->attach($interceptor);
			}
		}
	}

	/**
	 * Removes an interceptor to apply to values coming from object accessors.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeInterceptor($interceptor) {
		foreach ($interceptor->getInterceptionPoints() as $interceptionPoint) {
			if ($this->interceptors[$interceptionPoint]->contains($interceptor)) {
				$this->interceptors[$interceptionPoint]->detach($interceptor);
			}
		}
		
	}

	/**
	 * Returns all interceptors for a given Interception Point.
	 *
	 * @param int $interceptionPoint one of the \F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_* constants,
	 * @return \SplObjectStorage<\F3\Fluid\Core\Parser\InterceptorInterface>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getInterceptors($interceptionPoint) {
		if ($this->interceptors[$interceptionPoint] instanceof \SplObjectStorage) {
			return $this->interceptors[$interceptionPoint];
		}
		return new \SplObjectStorage();
	}

}
?>