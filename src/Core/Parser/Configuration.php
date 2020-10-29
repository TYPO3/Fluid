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
     * @var bool
     */
    protected $viewHelperArgumentEscapingEnabled = true;

    /**
     * Generic interceptors registered with the configuration.
     *
     * @var \SplObjectStorage[]
     */
    protected $interceptors = [];

    /**
     * Escaping interceptors registered with the configuration.
     *
     * @var \SplObjectStorage[]
     */
    protected $escapingInterceptors = [];

    /**
     * @return bool
     */
    public function isViewHelperArgumentEscapingEnabled()
    {
        return $this->viewHelperArgumentEscapingEnabled;
    }

    /**
     * @param bool $viewHelperArgumentEscapingEnabled
     */
    public function setViewHelperArgumentEscapingEnabled($viewHelperArgumentEscapingEnabled): void
    {
        $this->viewHelperArgumentEscapingEnabled = (bool) $viewHelperArgumentEscapingEnabled;
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
                $interceptorArray[$interceptionPoint] = new \SplObjectStorage();
            }
            $interceptors = $interceptorArray[$interceptionPoint];
            if (!$interceptors->contains($interceptor)) {
                $interceptors->attach($interceptor);
            }
        }
    }

    /**
     * Returns all interceptors for a given Interception Point.
     *
     * @param integer $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return InterceptorInterface[]
     */
    public function getInterceptors($interceptionPoint)
    {
        return isset($this->interceptors[$interceptionPoint]) ? $this->interceptors[$interceptionPoint] : new \SplObjectStorage();
    }

    /**
     * Returns all escaping interceptors for a given Interception Point.
     *
     * @param integer $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return InterceptorInterface[]
     */
    public function getEscapingInterceptors($interceptionPoint)
    {
        return isset($this->escapingInterceptors[$interceptionPoint]) ? $this->escapingInterceptors[$interceptionPoint] : new \SplObjectStorage();
    }
}
