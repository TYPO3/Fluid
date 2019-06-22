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
     * Adds an interceptor to apply to values coming from object accessors.
     *
     * @param InterceptorInterface $interceptor
     * @return void
     */
    public function addInterceptor(InterceptorInterface $interceptor): void
    {
        $this->addInterceptorToArray($interceptor, $this->interceptors);
    }

    /**
     * Adds an escaping interceptor to apply to values coming from object accessors if escaping is enabled
     *
     * @param InterceptorInterface $interceptor
     * @return void
     */
    public function addEscapingInterceptor(InterceptorInterface $interceptor): void
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
    protected function addInterceptorToArray(InterceptorInterface $interceptor, array &$interceptorArray): void
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
     * @return InterceptorInterface[]
     */
    public function getInterceptors(int $interceptionPoint): array
    {
        return isset($this->interceptors[$interceptionPoint]) ? $this->interceptors[$interceptionPoint] : [];
    }

    /**
     * Returns all escaping interceptors for a given Interception Point.
     *
     * @param integer $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return InterceptorInterface[]
     */
    public function getEscapingInterceptors(int $interceptionPoint): array
    {
        return isset($this->escapingInterceptors[$interceptionPoint]) ? $this->escapingInterceptors[$interceptionPoint] : [];
    }
}
