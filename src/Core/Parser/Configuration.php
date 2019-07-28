<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * The parser configuration. Contains all configuration needed to configure
 * the building of a SyntaxTree.
 */
class Configuration
{
    public const FEATURE_PARSING = 'parsing';
    public const FEATURE_ESCAPING = 'escaping';
    public const FEATURE_RUNTIME_CACHE = 'runtimeCache';

    /**
     * @var array
     */
    protected $features = [
        self::FEATURE_PARSING => true,
        self::FEATURE_ESCAPING => true,
        self::FEATURE_RUNTIME_CACHE => true,
    ];

    /**
     * @param string $feature
     * @param string|int|bool|null $state
     * @return bool
     */
    public function setFeatureState(string $feature, $state): bool
    {
        $previous = $this->features[$feature];
        if (is_bool($state) || is_numeric($state) || is_null($state)) {
            $this->features[$feature] = (bool)$state;
        } elseif (is_string($state)) {
            $this->features[$feature] = in_array(strtolower($state), ['on', 'true', 'enabled'], true);
        }
        return $previous;
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return $this->features[$feature];
    }
}
