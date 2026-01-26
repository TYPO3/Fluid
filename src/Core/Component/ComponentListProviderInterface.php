<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

/**
 * @api
 * @see AbstractComponentCollection
 */
interface ComponentListProviderInterface
{
    /**
     * Returns a list of component names that are available in a
     * component collection
     * @return string[]
     */
    public function getAvailableComponents(): array;
}
