<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * Base class for a collection of components: Fluid templates that can be called with Fluid's
 * ViewHelper syntax. The most basic implementation only needs to provide an implementation for
 * the abstract method getTemplatePaths(), as defined in ComponentResolverInterface.
 *
 * @api
 */
abstract class AbstractComponentCollection implements ViewHelperResolverDelegateInterface, ComponentResolverInterface
{
    /**
     * Runtime cache for component argument definitions. This is also necessary to short-circuit
     * argument retrieval if components are called recursively due to implementation details of
     * the TemplateParser.
     *
     * @var array<class-string, array<string, array<string, ArgumentDefinition>>>
     */
    protected array $argumentDefinitionsCache = [];

    /**
     * Overwrite this method if you want to provide additional variables to component views
     *
     * @return array<string, mixed>
     */
    public function getAdditionalVariables(): array
    {
        return [];
    }

    /**
     * Overwrite this method if you want to define default definitions that should be present in all components.
     *
     * @param string $viewHelperName             ViewHelper tag name from a template, e. g. atom.button
     * @return array<string, ArgumentDefinition>
     */
    public function getArgumentDefinitions(string $viewHelperName, ViewHelperResolver $viewHelperResolver): array
    {
        if (!isset($this->argumentDefinitionsCache[$viewHelperName])) {
            // This prevents issues when components are used recursively
            $this->argumentDefinitionsCache[$viewHelperName] = [];
            $templateName = $this->resolveTemplateName($viewHelperName);
            // @todo parsing a template should be possible without a rendering context and ViewHelperResolver
            $renderingContext = new RenderingContext();
            $renderingContext->setViewHelperResolver($viewHelperResolver->getScopedCopy());
            $parsedTemplate = $renderingContext->getTemplateParser()->parse(
                $this->getTemplatePaths()->getTemplateSource('Default', $templateName),
                $this->getTemplatePaths()->getTemplateIdentifier('Default', $templateName),
            );
            $this->argumentDefinitionsCache[$viewHelperName] = $parsedTemplate->getArgumentDefinitions();
        }
        return $this->argumentDefinitionsCache[$viewHelperName];
    }

    /**
     * Overwrite this method if you want to use a different folder structure for component templates
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return string                 Component template name to be used for this ViewHelper,
     *                                without format suffix, e. g. Atom/Button
     */
    public function resolveTemplateName(string $viewHelperName): string
    {
        $componentNameFragments = explode('.', $viewHelperName);
        return implode(DIRECTORY_SEPARATOR, array_map(ucfirst(...), $componentNameFragments));
    }

    final public function resolveViewHelperClassName(string $viewHelperName): string
    {
        $expectedTemplateName = $this->resolveTemplateName($viewHelperName);
        if (!$this->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $expectedTemplateName)) {
            throw new UnresolvableViewHelperException(sprintf(
                'Based on your spelling, the system would load the component template "%s", however this file does not exist.',
                $expectedTemplateName,
            ), 1748511297);
        }
        return ComponentAdapter::class;
    }

    final public function getNamespace(): string
    {
        return static::class;
    }
}
