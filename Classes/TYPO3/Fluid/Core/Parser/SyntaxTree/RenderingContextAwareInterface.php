<?php
namespace TYPO3\Fluid\Core\Parser\SyntaxTree;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Interface for objects which are aware of Fluid's rendering context. All objects
 * marked with this interface will get the current rendering context injected
 * by the ObjectAccessorNode on trying to evaluate them.
 *
 */
interface RenderingContextAwareInterface {

	/**
	 * Sets the current rendering context
	 *
	 * @param RenderingContextInterface $renderingContext
	 * @return void
	 */
	public function setRenderingContext(RenderingContextInterface $renderingContext);

}
