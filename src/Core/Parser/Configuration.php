<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

/**
 * The parser configuration. Contains all configuration needed to configure
 * the building of a SyntaxTree.
 */
class Configuration
{
    /**
     * @var bool
     */
    protected bool $viewHelperArgumentEscapingEnabled = true;

    /**
     * Generic interceptors registered with the configuration.
     *
     * @var InterceptorInterface[]
     */
    protected array $interceptors = [];

    /**
     * Escaping interceptors registered with the configuration.
     *
     * @var InterceptorInterface[]
     */
    protected array $escapingInterceptors = [];

    public function isViewHelperArgumentEscapingEnabled(): bool
    {
        return $this->viewHelperArgumentEscapingEnabled;
    }

    public function setViewHelperArgumentEscapingEnabled(bool $viewHelperArgumentEscapingEnabled): void
    {
        $this->viewHelperArgumentEscapingEnabled = $viewHelperArgumentEscapingEnabled;
    }

    /**
     * Adds an interceptor to apply to values coming from object accessors.
     */
    public function addInterceptor(InterceptorInterface $interceptor): void
    {
        $this->addInterceptorToArray($interceptor, $this->interceptors);
    }

    /**
     * Adds an escaping interceptor to apply to values coming from object accessors if escaping is enabled
     */
    public function addEscapingInterceptor(InterceptorInterface $interceptor): void
    {
        $this->addInterceptorToArray($interceptor, $this->escapingInterceptors);
    }

    /**
     * Adds an interceptor to apply to values coming from object accessors.
     */
    protected function addInterceptorToArray(InterceptorInterface $interceptor, array &$interceptorArray): void
    {
        foreach ($interceptor->getInterceptionPoints() as $interceptionPoint) {
            if (!isset($interceptorArray[$interceptionPoint])) {
                $interceptorArray[$interceptionPoint] = [];
            }
            $interceptors = &$interceptorArray[$interceptionPoint];
            if (!in_array($interceptor, $interceptors, true)) {
                $interceptors[] = $interceptor;
            }
        }
    }

    /**
     * Returns all interceptors for a given Interception Point.
     *
     * @param int $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return InterceptorInterface[]
     */
    public function getInterceptors(int $interceptionPoint): array
    {
        return $this->interceptors[$interceptionPoint] ?? [];
    }

    /**
     * Returns all escaping interceptors for a given Interception Point.
     *
     * @param int $interceptionPoint one of the InterceptorInterface::INTERCEPT_* constants,
     * @return InterceptorInterface[]
     */
    public function getEscapingInterceptors(int $interceptionPoint): array
    {
        return $this->escapingInterceptors[$interceptionPoint] ?? [];
    }
}
