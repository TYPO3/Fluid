<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Component;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Transparent Component Interface
 *
 * Signal interface which when implemented by a Component, causes
 * the Component to allow getNamedChild and getTypedChildren to
 * read potential matching Components from children.
 *
 * Normally, the methods are not recursive. This interface allows
 * them to recurse through any Component that implements it.
 */
interface TransparentComponentInterface
{
}
