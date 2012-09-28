<?php
namespace TYPO3\Fluid\ViewHelpers\Uri;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Email uri view helper.
 * Currently the specified email is simply prepended by "mailto:" but we might add spam protection.
 *
 * = Examples =
 *
 * <code title="basic email uri">
 * <f:uri.email email="foo@bar.tld" />
 * </code>
 * <output>
 * mailto:foo@bar.tld
 * </output>
 *
 * @api
 */
class EmailViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $email The email address to be turned into a mailto uri.
	 * @return string Rendered email uri
	 * @api
	 */
	public function render($email) {
		return 'mailto:' . $email;
	}
}


?>
