<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

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
        if (count($explodedViewHelperName) > 1) {
            $className = implode('\\', array_map('ucfirst', $explodedViewHelperName));
        } else {
            $className = ucfirst($explodedViewHelperName[0]);
        }
        $fullClassName = $this->namespace . '\\' . $className . 'ViewHelper';
        if (!class_exists($fullClassName)) {
            throw new UnresolvableViewHelperException(sprintf(
                'Based on your spelling, the system would load the class "%s", however this class does not exist.',
                $fullClassName
            ), 1746975989);
        }
        return $fullClassName;
    }

    public function __toString(): string
    {
        return $this->namespace;
    }
}
