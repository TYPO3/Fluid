<?php
namespace TYPO3\Fluid\ViewHelpers;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which creates a <base href="..." /> tag. The Base URI
 * is taken from the current request.
 * In TYPO3 Flow, this ViewHelper is no longer required to make the links work.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:base />
 * </code>
 * <output>
 * <base href="http://yourdomain.tld/" />
 * (depending on your domain)
 * </output>
 *
 * @deprecated since 2.1.0 this ViewHelper is no longer required for regular links and forms
 */
class BaseViewHelper extends AbstractViewHelper {

	/**
	 * Render the "Base" tag by outputting $httpRequest->getBaseUri()
	 *
	 * @return string "base"-Tag.
	 * @api
	 */
	public function render() {
		return '<base href="' . $this->controllerContext->getRequest()->getHttpRequest()->getBaseUri() . '" />';
	}
}
