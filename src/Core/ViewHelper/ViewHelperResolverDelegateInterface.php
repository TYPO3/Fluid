<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * The ViewHelperResolverDelegateInterface can be used to provide custom
 * ViewHelper resolving to a specific ViewHelper namespace. If a ViewHelper
 * namespace is registered, Fluid first checks if the provided class string
 * represents an actual PHP class (which must then implement this interface)
 * or if it's just a partial PHP namespace that refers to multiple ViewHelper
 * files. The latter will be handled by the fallback implementation
 * TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperCollection.
 *
 * @api
 */
interface ViewHelperResolverDelegateInterface
{
    /**
     * Resolves the appropriate ViewHelper PHP class for the given
     * ViewHelper name, throws exception if no class can be resolved.
     *
     * @throws UnresolvableViewHelperException
     * @param string $name   ViewHelper name as written in the template,
     *                       e. g. "format.trim"
     * @return class-string  ViewHelper class as full PHP namespace
     */
    public function resolveViewHelperClassName(string $name): string;

    /**
     * Returns the PHP namespace this delegate has been registered for
     * This string representation will be used to restore the delegate
     * object from the cache in the future.
     *
     * @return class-string
     */
    public function getNamespace(): string;
}
