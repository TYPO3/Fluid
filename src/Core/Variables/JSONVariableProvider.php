<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Variables;

/**
 * Class JSONVariableProvider
 *
 * VariableProvider capable of using JSON files
 * and streams as data source.
 */
class JSONVariableProvider extends StandardVariableProvider implements VariableProviderInterface
{
    protected int $lastLoaded = 0;

    /**
     * Lifetime of fetched JSON sources before refetch. Using
     * a hard value avoids the need to re-query using HEAD and
     * should allow any HTTPD process to finish in time but make
     * any CLI/infinite running scripts re-fetch JSON after this
     * time has passed.
     */
    protected int $ttl = 15;

    /**
     * JSON source. Either a complete JSON string with an object
     * inside, or a reference to a JSON file either local or
     * remote (supporting any stream types PHP supports).
     */
    protected string $source;

    public function getSource(): mixed
    {
        return $this->source;
    }

    public function setSource(mixed $source): void
    {
        $this->source = $source;
    }

    public function getAll(): array
    {
        $this->load();
        return parent::getAll();
    }

    public function get(string $identifier): mixed
    {
        $this->load();
        return parent::get($identifier);
    }

    public function getByPath(string $path): mixed
    {
        $this->load();
        return parent::getByPath($path);
    }

    public function getAllIdentifiers(): array
    {
        $this->load();
        return parent::getAllIdentifiers();
    }

    protected function load(): void
    {
        if ($this->source !== null && time() > ($this->lastLoaded + $this->ttl)) {
            if (!$this->isJSON($this->source)) {
                $source = file_get_contents($this->source);
            } else {
                $source = $this->source;
            }
            $this->variables = json_decode($source, true);
            $this->lastLoaded = time();
        }
    }

    protected function isJSON(string $string): bool
    {
        $string = trim($string);
        return $string[0] === '{' && substr($string, -1) === '}';
    }
}
