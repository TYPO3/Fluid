<?php
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\AbstractComponent;

/**
 * STUB CLASS: ParsingState, functionally almost equivalent to EntryNode
 * in that it behaves like the "root holder" object Fluid uses. Allows
 *
 * Exists to support Interceptors written for Fluid 2.x; an instance of
 * the "root entry node" will be passed to interceptors as replacement
 * for the ParsingState instance that is passed in Fluid 2.x. Allows the
 * Interceptor to preserve method signature for the "process" method.
 *
 * The same context info can be extracted from EntryNode as could be
 * extracted from ParsingState, although Interceptors that need to be
 * compatible with Fluid 2.x and 3.x and which need to access context
 * information, may need to implement additional checks (instanceof) in
 * order to assert whether Fluid 2.x or 3.x is in use.
 *
 * @deprecated Will be removed in Fluid 4.0
 */
class ParsingState extends AbstractComponent
{
}
