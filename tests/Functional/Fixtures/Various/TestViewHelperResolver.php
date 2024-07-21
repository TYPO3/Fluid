<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MutableTestViewHelper;

/**
 * ViewHelperResolver with overridable resolving
 *
 * Allows a specific ViewHelper instance to be returned for
 * `test:test`.
 *
 * Clones are returned from createViewHelperInstance in order
 * to allow using the same dummy ViewHelper in multiple calls
 * in the same parsing run, without tainting the internal
 * properties of the ViewHelper.
 *
 * The resolver will reply "yes, namespace known" when asked
 * if a namespace "test" exists.
 */
class TestViewHelperResolver extends ViewHelperResolver
{
    /**
     * @var ViewHelperInterface|null
     */
    private $override;

    public function overrideResolving(ViewHelperInterface $instance)
    {
        $this->override = $instance;
    }

    public function isNamespaceValid(string $namespaceIdentifier): bool
    {
        return $namespaceIdentifier === 'test' || parent::isNamespaceValid($namespaceIdentifier);
    }

    public function isNamespaceIgnored(string $namespaceIdentifier): bool
    {
        if ($namespaceIdentifier === 'test') {
            return false;
        }
        return parent::isNamespaceIgnored($namespaceIdentifier);
    }

    public function resolveViewHelperClassName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        return $namespaceIdentifier === 'test' && $methodIdentifier === 'test' && $this->override !== null ? get_class($this->override) : parent::resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
    }

    public function createViewHelperInstanceFromClassName(string $viewHelperClassName): ViewHelperInterface
    {
        if ($viewHelperClassName === MutableTestViewHelper::class) {
            return $this->override;
        }
        return parent::createViewHelperInstanceFromClassName($viewHelperClassName);
    }

    public function createViewHelperInstance(string $namespace, string $viewHelperShortName): ViewHelperInterface
    {
        return $namespace === 'test' && $viewHelperShortName === 'test' && $this->override !== null ? clone $this->override : parent::createViewHelperInstance($namespace, $viewHelperShortName);
    }
}
