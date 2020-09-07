<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Variables;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class JSONVariableProvider
 *
 * VariableProvider capable of using JSON files
 * and streams as data source.
 */
class JSONVariableProvider extends StandardVariableProvider implements VariableProviderInterface
{

    /**
     * @var integer
     */
    protected $lastLoaded = 0;

    /**
     * Lifetime of fetched JSON sources before refetch. Using
     * a hard value avoids the need to re-query using HEAD and
     * should allow any HTTPD process to finish in time but make
     * any CLI/infinite running scripts re-fetch JSON after this
     * time has passed.
     *
     * @var integer
     */
    protected $ttl = 15;

    /**
     * JSON source. Either a complete JSON string with an object
     * inside, or a reference to a JSON file either local or
     * remote (supporting any stream types PHP supports).
     *
     * @var string
     */
    protected $source = null;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     * @return void
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $this->load();
        return parent::getAll();
    }

    public function getByPath(string $path, array $accessors = [])
    {
        $this->load();
        return parent::getByPath($path, $accessors);
    }

    /**
     * @param string $identifier
     * @return mixed
     */
    public function get(string $identifier)
    {
        $this->load();
        return parent::get($identifier);
    }

    /**
     * @return array
     */
    public function getAllIdentifiers(): array
    {
        $this->load();
        return parent::getAllIdentifiers();
    }

    /**
     * @return void
     */
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

    /**
     * @param string $string
     * @return boolean
     */
    protected function isJSON(string $string): bool
    {
        $string = trim($string);
        return ($string[0] === '{' && substr($string, -1) === '}');
    }
}
