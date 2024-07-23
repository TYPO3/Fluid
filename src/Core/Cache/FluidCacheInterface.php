<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Cache;

/**
 * Interface FluidCacheInterface
 *
 * Implemented by classes providing caching
 * features for the Fluid templates being rendered.
 */
interface FluidCacheInterface
{
    /**
     * Gets an entry from the cache or null if the
     * entry does not exist.
     */
    public function get(string $name): mixed;

    /**
     * Set or updates an entry identified by $name
     * into the cache.
     */
    public function set(string $name, mixed $value): void;

    /**
     * Flushes the cache either by entry or flushes
     * the entire cache if no entry is provided.
     */
    public function flush(?string $name = null): void;

    public function getCacheWarmer(): FluidCacheWarmerInterface;
}
