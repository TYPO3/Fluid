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

use TYPO3\Flow\Annotations as Flow;

/**
 * This interface should be implemented by proxy objects which want to return
 * something different than themselves when being part of a Fluid ObjectAccess
 * chain such as {foo.bar.baz}.
 *
 * It consists only of one method "objectAccess()", which is called whenever an object
 * implementing this interface is encountered at a property path. The return value of this
 * method is the basis for further object accesses; so the object effectively "replaces" itself.
 *
 * Example: If the object at "foo.bar" implements this interface, the "objectAccess()" method
 * is called after evaluating foo.bar; and the returned value is then traversed to "baz".
 *
 * Often it can make sense to implement this interface alongside with the ArrayAccess interface.
 *
 * It is currently used *internally* and might change without further notice.
 */
interface TemplateObjectAccessInterface {

	/**
	 * Post-Processor which is called whenever this object is encountered in a Fluid
	 * object access.
	 *
	 * @return mixed the value which should be returned to the caller, or which should be traversed further.
	 */
	public function objectAccess();

}
