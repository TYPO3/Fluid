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
	 * Value interceptors (applied to values coming from object accessors)
	 * registered with the configuration.
	 * @var \SplObjectStorage
	 */
	protected $valueInterceptors;

	/**
	 * text interceptors (applied to values coming from text nodes)
	 * registered with the configuration.
	 * @var \SplObjectStorage
	 */
	protected $textInterceptors;

	/**
	 * Set up the internals...
	 *
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function __construct() {
		$this->valueInterceptors = new \SplObjectStorage();
		$this->textInterceptors = new \SplObjectStorage();
	}

	/**
	 * Adds an interceptor to apply to values coming from object accessors.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addValueInterceptor(\F3\Fluid\Core\Parser\InterceptorInterface $interceptor) {
		if (!$this->valueInterceptors->contains($interceptor)) {
			$this->valueInterceptors->attach($interceptor);
		}
	}

	/**
	 * Removes an interceptor to apply to values coming from object accessors.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeValueInterceptor($interceptor) {
		if ($this->valueInterceptors->contains($interceptor)) {
			$this->valueInterceptors->detach($interceptor);
		}
	}

	/**
	 * Returns all interceptors to apply to values coming from object accessors.
	 *
	 * @return \SplObjectStorage<\F3\Fluid\Core\Parser\InterceptorInterface>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getValueInterceptors() {
		return $this->valueInterceptors;
	}

	/**
	 * Adds an interceptor to apply to values coming from text nodes.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addTextInterceptor($interceptor) {
		if (!$this->textInterceptors->contains($interceptor)) {
			$this->textInterceptors->attach($interceptor);
		}
	}

	/**
	 * Removes an interceptor to apply to values coming from text nodes.
	 *
	 * @param \F3\Fluid\Core\Parser\InterceptorInterface $interceptor
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function removeTextInterceptor($interceptor) {
		if ($this->textInterceptors->contains($interceptor)) {
			$this->textInterceptors->detach($interceptor);
		}
	}

	/**
	 * Returns all interceptors to apply to values coming from text nodes.
	 *
	 * @return \SplObjectStorage<\F3\Fluid\Core\Parser\InterceptorInterface>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getTextInterceptors() {
		return $this->textInterceptors;
	}

}
?>