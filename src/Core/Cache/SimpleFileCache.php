<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Cache;

/**
 * Class SimpleFileCache
 *
 * The most basic form of cache for Fluid
 * templates: storing the compiled PHP code
 * as a file that can be included via the
 * get() method.
 */
class SimpleFileCache implements FluidCacheInterface
{
    /**
     * Default cache directory is in "cache/"
     * relative to the point of script execution.
     */
    public const DIRECTORY_DEFAULT = 'cache';

    /**
     * @var string
     */
    protected string $directory = self::DIRECTORY_DEFAULT;

    public function __construct(string $directory = self::DIRECTORY_DEFAULT)
    {
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * Get an instance of FluidCacheWarmerInterface which
     * can warm up template files that would normally be
     * cached on-the-fly to this FluidCacheInterface
     * implementaion.
     */
    public function getCacheWarmer(): FluidCacheWarmerInterface
    {
        return new StandardCacheWarmer();
    }

    /**
     * Gets an entry from the cache or null if the
     * entry does not exist. Returns true if the cached
     * class file was included, false if it does not
     * exist in the cache directory.
     */
    public function get(string $name): bool
    {
        if (class_exists($name)) {
            return true;
        }
        $file = $this->getCachedFilePathAndFilename($name);
        if (file_exists($file)) {
            include_once $file;
            return true;
        }
        return false;
    }

    /**
     * Set or updates an entry identified by $name
     * into the cache.
     *
     * @throws \RuntimeException
     */
    public function set(string $name, mixed $value): void
    {
        if (!file_exists(rtrim($this->directory, '/'))) {
            throw new \RuntimeException(sprintf('Invalid Fluid cache directory - %s does not exist!', $this->directory));
        }
        file_put_contents($this->getCachedFilePathAndFilename($name), $value);
    }

    /**
     * Flushes the cache either by entry or flushes
     * the entire cache if no entry is provided.
     */
    public function flush(?string $name = null): void
    {
        if ($name !== null) {
            $this->flushByName($name);
        } else {
            $files = $this->getCachedFilenames();
            array_walk($files, [$this, 'flushByFilename']);
        }
    }

    protected function getCachedFilenames(): array
    {
        return glob($this->directory . '*.php');
    }

    protected function flushByName(string $name): void
    {
        $this->flushByFilename($this->getCachedFilePathAndFilename($name));
    }

    protected function flushByFilename(string $filename): void
    {
        unlink($filename);
    }

    protected function getCachedFilePathAndFilename(string $identifier): string
    {
        return $this->directory . $identifier . '.php';
    }
}
