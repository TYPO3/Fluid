<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Default ViewHelper resolving implementation that is used if
 * a PHP namespace prefix is given that doesn't have a custom
 * ViewHelperResolverDelegate implementation.
 */
final readonly class ViewHelperCollection implements ViewHelperResolverDelegateInterface
{
    private string $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = rtrim($namespace, '\\');
    }

    public function resolveViewHelperClassName(string $name): string
    {
        $explodedViewHelperName = explode('.', $name);
        $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
        $fullClassName = $this->namespace . '\\' . $className . 'ViewHelper';
        if (!class_exists($fullClassName)) {
            throw new UnresolvableViewHelperException(sprintf(
                'Based on your spelling, the system would load the class "%s", however this class does not exist.',
                $fullClassName,
            ), 1746975989);
        }
        return $fullClassName;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
