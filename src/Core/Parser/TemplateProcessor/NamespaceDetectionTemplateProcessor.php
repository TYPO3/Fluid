<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\Patterns;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This template processor takes care of the following things:
 *
 *   - register/ignore namespaces through xmlns and shorthand syntax
 *   - report any unregistered/unignored namespaces through exception
 */
class NamespaceDetectionTemplateProcessor implements TemplateProcessorInterface
{
    public const NAMESPACE_DECLARATION = '/(?<!\\\\){namespace\s*(?P<identifier>[a-zA-Z\*]+[a-zA-Z0-9\.\*]*)\s*(=\s*(?P<phpNamespace>(?:[A-Za-z0-9\.]+|Tx)(?:\\\\\w+)+)\s*)?}/m';

    protected RenderingContextInterface $renderingContext;

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Pre-process the template source before it is
     * returned to the TemplateParser or passed to
     * the next TemplateProcessorInterface instance.
     */
    public function preProcessSource(string $templateSource): string
    {
        $templateSource = $this->registerNamespacesFromTemplateSource($templateSource);
        return $templateSource;
    }

    /**
     * Register all namespaces that are declared inside the template string
     */
    public function registerNamespacesFromTemplateSource(string $templateSource): string
    {
        $viewHelperResolver = $this->renderingContext->getViewHelperResolver();
        $matches = [];
        $namespacePattern = 'xmlns:([a-zA-Z0-9\.]+)=("[^"]+"|\'[^\']+\')+';
        $matched = preg_match('/<([a-z0-9]+)(?:[^>]*?)\\s+' . $namespacePattern . '[^>]*>/', $templateSource, $matches);

        if ($matched) {
            $namespaces = [];
            preg_match_all('/' . $namespacePattern . '/', $matches[0], $namespaces, PREG_SET_ORDER);
            foreach ($namespaces as $set) {
                $namespaceUrl = trim($set[2], '"\'');
                if (str_starts_with($namespaceUrl, Patterns::NAMESPACEPREFIX)) {
                    $namespaceUri = substr($namespaceUrl, 20);
                    $namespacePhp = str_replace('/', '\\', $namespaceUri);
                } elseif (str_starts_with($namespaceUrl, Patterns::NAMESPACEPREFIX_INVALID)) {
                    throw new Exception(
                        'Invalid Fluid namespace definition detected: ' . $namespaceUrl . '. Namespaces must always start with ' . Patterns::NAMESPACEPREFIX . '.',
                        1721467847,
                    );
                } elseif (!preg_match('/([^a-z0-9_\\\\]+)/i', $namespaceUrl)) {
                    $namespacePhp = $namespaceUrl;
                    $namespacePhp = preg_replace('/\\\\{2,}/', '\\', $namespacePhp);
                } else {
                    $namespacePhp = null;
                }
                $viewHelperResolver->addLocalNamespace($set[1], $namespacePhp);
            }
            if (strpos($matches[0], 'data-namespace-typo3-fluid="true"')) {
                $templateSource = str_replace($matches[0], '', $templateSource);
                $closingTagName = $matches[1];
                $closingTag = '</' . $closingTagName . '>';
                if (strpos($templateSource, $closingTag)) {
                    $templateSource = substr($templateSource, 0, strrpos($templateSource, $closingTag)) .
                        substr($templateSource, strrpos($templateSource, $closingTag) + strlen($closingTag));
                }
            } else {
                $namespaceAttributesToRemove = [];
                foreach ($namespaces as $namespace) {
                    if (!$viewHelperResolver->isNamespaceIgnored($namespace[1])) {
                        $namespaceAttributesToRemove[] = preg_quote($namespace[1], '/') . '="' . preg_quote($namespace[2], '/') . '"';
                    }
                }
                if (count($namespaceAttributesToRemove)) {
                    $matchWithRemovedNamespaceAttributes = preg_replace('/(?:\\s*+xmlns:(?:' . implode('|', $namespaceAttributesToRemove) . ')\\s*+)++/', ' ', $matches[0]);
                    $templateSource = str_replace($matches[0], $matchWithRemovedNamespaceAttributes, $templateSource);
                }
            }
        }

        preg_match_all(static::NAMESPACE_DECLARATION, $templateSource, $namespaces);
        if (!empty($namespaces['identifier'])) {
            // There are no namespace declarations using curly-brace syntax.
            foreach ($namespaces['identifier'] as $key => $identifier) {
                $namespace = $namespaces['phpNamespace'][$key];
                if (strlen($namespace) === 0) {
                    $namespace = null;
                }
                $viewHelperResolver->addLocalNamespace($identifier, $namespace);
            }
            foreach ($namespaces[0] as $removal) {
                $templateSource = str_replace($removal, '', $templateSource);
            }
        }

        return $templateSource;
    }
}
