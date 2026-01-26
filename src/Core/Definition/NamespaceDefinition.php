<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Definition;

use TYPO3Fluid\Fluid\Core\Parser\Patterns;

/**
 * @api
 */
final readonly class NamespaceDefinition
{
    private string $namespace;

    /**
     * @param ViewHelperDefinitionInterface[] $viewHelpers
     */
    public function __construct(
        string $namespace,
        private ?string $alias,
        private iterable $viewHelpers,
    ) {
        $this->namespace = rtrim($namespace, '\\');
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getXmlNamespace(): string
    {
        return Patterns::NAMESPACEPREFIX . str_replace('\\', '/', $this->namespace);
    }

    public function getViewHelpers(): iterable
    {
        return $this->viewHelpers;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
