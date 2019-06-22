<?php
namespace TYPO3Fluid\Fluid\Core\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

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
    const DIRECTORY_DEFAULT = 'cache';

    /**
     * @var string
     */
    protected $directory = self::DIRECTORY_DEFAULT;

    /**
     * @param string $directory
     */
    public function __construct(string $directory = self::DIRECTORY_DEFAULT)
    {
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * Get an instance of FluidCacheWarmerInterface which
     * can warm up template files that would normally be
     * cached on-the-fly to this FluidCacheInterface
     * implementaion.
     *
     * @return FluidCacheWarmerInterface
     */
    public function getCacheWarmer(): FluidCacheWarmerInterface
    {
        return new StandardCacheWarmer();
    }

    /**
     * Gets an entry from the cache or NULL if the
     * entry does not exist. Returns TRUE if the cached
     * class file was included, FALSE if it does not
     * exist in the cache directory.
     *
     * @param string $name
     * @return boolean
     */
    public function get(string $name): bool
    {
        if (class_exists($name)) {
            return true;
        }
        $file = $this->getCachedFilePathAndFilename($name);
        if (file_exists($file) && !class_exists($name)) {
            include_once $file;
            return true;
        }
        return false;
    }

    /**
     * Set or updates an entry identified by $name
     * into the cache.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws \RuntimeException
     */
    public function set(string $name, $value): void
    {
        if (!file_exists(rtrim($this->directory, '/'))) {
            throw new \RuntimeException(sprintf('Invalid Fluid cache directory - %s does not exist!', $this->directory));
        }
        file_put_contents($this->getCachedFilePathAndFilename($name), $value);
    }

    /**
     * Flushes the cache either by entry or flushes
     * the entire cache if no entry is provided.
     *
     * @param string|NULL $name
     * @return void
     */
    public function flush(?string $name = null): void
    {
        if ($name !== null) {
            $this->flushByName($name);
        } else {
            $files = $this->getCachedFilenames();
            if (is_array($files)) {
                array_walk($files, [$this, 'flushByFilename']);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    protected function getCachedFilenames(): array
    {
        return glob($this->directory . '*.php');
    }

    /**
     * @param string $name
     * @return void
     */
    protected function flushByName(string $name): void
    {
        $this->flushByFilename($this->getCachedFilePathAndFilename($name));
    }

    /**
     * @param string $filename
     * @return void
     */
    protected function flushByFilename(string $filename): void
    {
        unlink($filename);
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function getCachedFilePathAndFilename(string $identifier): string
    {
        return $this->directory . $identifier . '.php';
    }
}
