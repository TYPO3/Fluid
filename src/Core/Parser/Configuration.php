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
     * Use Sequencer-based parsing as substitute for the old regular expression
     * based parsing. Can be set to "false" to use the old parser instead.
     *
     * @var bool
     */
    protected $useSequencer = true;

    public function getUseSequencer(): bool
    {
        return $this->useSequencer;
    }

    public function setUseSequencer(bool $useSequencer): void
    {
        $this->useSequencer = $useSequencer;
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
