<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Embedded Component Interface
 *
 * Signaling interface for Components. Any Component that
 * implements this interface will not be evaluated when
 * evaluateChildNodes() is called - but is still fully
 * possible to extract with getNamedChild / getTypedChildren
 * (and then render directly if necessary).
 */
interface EmbeddedComponentInterface
{
}
