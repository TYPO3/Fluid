<?php
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
    public const FEATURE_SEQUENCER = 'sequencer';

    /**
     * Generic interceptors registered with the configuration.
     *
     * @var array
     */
    protected $interceptors = [];

    /**
     * Escaping interceptors registered with the configuration.
     *
     * @var array
     */
    protected $escapingInterceptors = [];

    /**
     * @var array
     */
    protected $features = [
        self::FEATURE_PARSING => true,
        self::FEATURE_ESCAPING => true,
        self::FEATURE_SEQUENCER => false,
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
            $this->features[$feature] = in_array(strtolower($state), ['on', 'true', 'enabled']);
        }
        return $previous;
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return $this->features[$feature];
    }

    /**
     * Adds an interceptor to apply to values coming from object accessors.
     *
     * @param InterceptorInterface $interceptor
     * @return void
     */
    public function addInterceptor(InterceptorInterface $interceptor)
    {
        $this->addInterceptorToArray($interceptor, $this->interceptors);
    }

    /**
     * Adds an escaping interceptor to apply to values coming from object accessors if escaping is enabled
     *
     * @param InterceptorInterface $interceptor
     * @return void
     */
    public function addEscapingInterceptor(InterceptorInterface $interceptor)
    {
        $this->addInterceptorToArray($interceptor, $this->escapingInterceptors);
    }

    /**
     * Adds an interceptor to apply to values coming from object accessors.
     *
     * @param InterceptorInterface $interceptor
     * @param \SplObjectStorage[] $interceptorArray
     * @return void
     */
    protected function addInterceptorToArray(InterceptorInterface $interceptor, array &$interceptorArray)
    {
        foreach ($interceptor->getInterceptionPoints() as $interceptionPoint) {
            if (!isset($interceptorArray[$interceptionPoint])) {
                $interceptorArray[$interceptionPoint] = [];
            }
            $interceptorClass = get_class($interceptor);
            $interceptorArray[$interceptionPoint][$interceptorClass] = $interceptor;
        }
    }

    /**
     * Returns all interceptors for a given Interception Point.
     *
     * @param integer $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return array
     */
    public function getInterceptors($interceptionPoint)
    {
        return isset($this->interceptors[$interceptionPoint]) ? $this->interceptors[$interceptionPoint] : [];
    }

    /**
     * Returns all escaping interceptors for a given Interception Point.
     *
     * @param integer $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return array
     */
    public function getEscapingInterceptors($interceptionPoint)
    {
        return isset($this->escapingInterceptors[$interceptionPoint]) ? $this->escapingInterceptors[$interceptionPoint] : [];
    }
}
