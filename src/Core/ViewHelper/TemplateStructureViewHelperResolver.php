<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/**
 * Special ViewHelperResolver implementation which only resolve those ViewHelpers
 * that are necessary to understand a basic structure of a template:
 * 1. Is there a layout?
 * 2. Are there any sections?
 * 3. Are there argument definitions?
 * 4. Are there any slots?
 * This prevents the parser from resolving _any_ ViewHelpers (both first and third party).
 * Note that this ViewHelperResolver results in templates that are not feasible for rendering
 * and should thus not be compiled/cached or rendered.
 *
 * @internal
 * @todo This logic should be an early parsing step in the TemplateParser.
 */
final class TemplateStructureViewHelperResolver extends ViewHelperResolver
{
    private const STRUCTURE_VIEWHELPERS = [
        'layout',
        'section',
        'argument',
        'slot',
    ];

    public function isNamespaceValid(string $namespaceIdentifier): bool
    {
        return $namespaceIdentifier === 'f';
    }

    public function isNamespaceIgnored(string $namespaceIdentifier): bool
    {
        return $namespaceIdentifier !== 'f';
    }

    public function resolveViewHelperClassName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        if ($namespaceIdentifier === 'f' && in_array($methodIdentifier, self::STRUCTURE_VIEWHELPERS)) {
            return parent::resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
        }
        return TemplateStructurePlaceholderViewHelper::class;
    }

    public function getResponsibleDelegate(string $namespaceIdentifier, string $methodIdentifier): ?ViewHelperResolverDelegateInterface
    {
        if ($namespaceIdentifier === 'f' && in_array($methodIdentifier, self::STRUCTURE_VIEWHELPERS)) {
            return parent::getResponsibleDelegate($namespaceIdentifier, $methodIdentifier);
        }
        return null;
    }
}
