<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\View\Aspect;

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
 * Caching of parseTemplate() call on the TemplateView.
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @aspect
 */
class TemplateViewCachingAspect {

	/**
	 * @var \F3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $findMatchResultsCache;

	/**
	 * @var \F3\FLOW3\Cache\Frontend\StringFrontend
	 */
	protected $resolveCache;

	/**
	 * Syntax tree cache. The key will be the file name (including path), the value the generated syntax tree.
	 * @var array
	 */
	protected $localSyntaxTreeCache = array();

	/**
	 * Syntax tree cache (persistent)
	 * @var \F3\FLOW3\Cache\Frontend\VariableFrontend
	 */
	protected $syntaxTreeCache;

	/**
	 * Injects the syntaxTreeCache
	 *
	 * @param \F3\FLOW3\Cache\Frontend\VariableFrontend $cache Cache for the reflection service
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function injectSyntaxTreeCache(\F3\FLOW3\Cache\Frontend\VariableFrontend $syntaxTreeCache) {
		$this->syntaxTreeCache = $syntaxTreeCache;
	}

	/**
	 * Around advice. Caches calls of parseTemplate() in classes implementing F3\Fluid\View\TemplateViewInterface.
	 * This advice is only active if Fluid.syntaxTreeCache.enable is TRUE.
	 *
	 * @around within(F3\Fluid\View\TemplateViewInterface) && method(.*->parseTemplate()) && setting(Fluid.syntaxTreeCache.enable)
	 * @param F3\FLOW3\AOP\JoinPointInterface $joinPoint The current join point
	 * @return \F3\Fluid\Core\Parser\ParsedTemplateInterface template tree
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function cacheParseTemplateCall(\F3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$templatePathAndFilename = $joinPoint->getMethodArgument('templatePathAndFilename');
		if (array_key_exists($templatePathAndFilename, $this->localSyntaxTreeCache)) {
			return $this->localSyntaxTreeCache[$templatePathAndFilename];
		}
		$cacheIdentifier = md5($templatePathAndFilename);
		if ($this->syntaxTreeCache->has($cacheIdentifier)) {
			return $this->syntaxTreeCache->get($cacheIdentifier);
		}
		$parsedTemplate = $joinPoint->getAdviceChain()->proceed($joinPoint);

		$this->syntaxTreeCache->set($cacheIdentifier, $parsedTemplate);
		$this->localSyntaxTreeCache[$templatePathAndFilename] = $parsedTemplate;
		return $parsedTemplate;
	}
}
?>
