<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use ReflectionMethod;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateStructureViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\UnresolvableViewHelperException;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Base class for a collection of components: Fluid templates that can be called with Fluid's
 * ViewHelper syntax. The most basic extending class only needs to provide an implementation for
 * the abstract method getTemplatePaths(), as defined in ComponentTemplateResolverInterface.
 *
 * @api
 */
abstract class AbstractComponentCollection implements ViewHelperResolverDelegateInterface, ComponentDefinitionProviderInterface, ComponentTemplateResolverInterface, ComponentListProviderInterface
{
    /**
     * Runtime cache for component definitions. This mainly speeds up uncached templates since we
     * create a new TemplateParser instance for each component to receive its argument definitions.
     *
     * @var array<string, ComponentDefinition>
     */
    private array $componentDefinitionsCache = [];

    /**
     * Overwrite this method if you want to use a different folder structure for component templates.
     * Note that getAvailableComponents() might need adjustment as well to get consistent results.
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return string                 Component template name to be used for this ViewHelper,
     *                                without format suffix, e. g. Atom/Button/Button
     * @see getAvailableComponents()
     */
    public function resolveTemplateName(string $viewHelperName): string
    {
        $fragments = array_map(ucfirst(...), explode('.', $viewHelperName));
        $name = array_pop($fragments);
        $path = implode('/', $fragments);
        return ($path !== '' ? $path . '/' : '') . $name . '/' . $name;
    }

    /**
     * Discovers all available components in the collection. Note that this default implementation
     * assumes the same folder structure as the default implementation of resolveTemplateName().
     *
     * @return string[]
     * @see resolveTemplateName()
     */
    public function getAvailableComponents(): array
    {
        /**
         * To not return an inconsistent result for existing component collections that use a different
         * folder structure by overriding resolveTemplateName(), we check if that method has been
         * overridden and then default to an empty array. In those cases, a custom getAvailableComponents()
         * needs to be implemented by the component collection.
         *
         * @todo remove this in Fluid 6 and add notice to changelog that getAvailableComponents()
         *       and resolveTemplateName() must be implemented consistently.
         */
        if ((new ReflectionMethod($this, 'resolveTemplateName'))->getDeclaringClass()->getName() !== self::class) {
            return [];
        }
        $availableTemplates = $this->getTemplatePaths()->resolveAvailableTemplateFiles(null);
        $fallbackFileExtension = '.' . $this->getTemplatePaths()->getFormat();
        $fullFileExtension = '.' . TemplatePaths::FLUID_EXTENSION . $fallbackFileExtension;
        $availableComponents = [];
        foreach ($availableTemplates as $templatePath) {
            // Remove file extension
            if (str_ends_with($templatePath, $fullFileExtension)) {
                $templatePath = substr($templatePath, 0, -strlen($fullFileExtension));
            } elseif (str_ends_with($templatePath, $fallbackFileExtension)) {
                $templatePath = substr($templatePath, 0, -strlen($fallbackFileExtension));
            }
            // Remove template root path
            foreach ($this->getTemplatePaths()->getTemplateRootPaths() as $rootPath) {
                if (str_starts_with($templatePath, $rootPath)) {
                    $templatePath = substr($templatePath, strlen($rootPath));
                    break;
                }
            }
            // Convert template name into ViewHelper name and validate directory structure
            // (resolveTemplateName() in reverse)
            $fragments = explode('/', $templatePath);
            $name1 = array_pop($fragments);
            $name2 = array_pop($fragments);
            if ($name1 !== $name2) {
                continue;
            }
            $fragments[] = $name2;
            $availableComponents[] = implode('.', array_map(lcfirst(...), $fragments));
        }
        return $availableComponents;
    }

    /**
     * Overwrite this method if you want to provide additional variables to component views
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return array<string, mixed>
     */
    public function getAdditionalVariables(string $viewHelperName): array
    {
        return [];
    }

    /**
     * Overwrite this method if you want components to be able to receive additional (non-registered)
     * arguments
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     */
    protected function additionalArgumentsAllowed(string $viewHelperName): bool
    {
        return false;
    }

    /**
     * Fetches the component definition (arguments, slots) for a ViewHelper call by
     * parsing the underlying Fluid template
     *
     * @todo we might introduce a separate exception here and catch internal exceptions,
     *       e. g. if invalid template is supplied
     */
    final public function getComponentDefinition(string $viewHelperName): ComponentDefinition
    {
        if (!isset($this->componentDefinitionsCache[$viewHelperName])) {
            $templateName = $this->resolveTemplateName($viewHelperName);
            $renderingContext = new RenderingContext();
            // At this stage, the component template needs to be parsed to gather the component's definition,
            // such as argument definitions and available slots. Ideally, this is done without any additional state
            // present, so with an "empty" RenderingContext. Due to the current state of the TemplateParser,
            // we currently have several bad alternatives, of which only one (4.) really works:
            // 1. Suppress exceptions during parsing, e. g. for undefined ViewHelpers by enabling the
            //    TolerantErrorHandler. This currently doesn't work because exceptions with closing ViewHelper
            //    tags aren't intercepted properly by the parser and bubble up, which results in an invalid
            //    parsed template.
            // 2. Suppress execution of all third-party ViewHelpers by removing the NamespaceDetectionTemplateProcessor
            //    (so that no namespaces can be added in the template) and defining all namespaces that aren't "f" as
            //    ignored (to prevent parser exceptions): $viewHelperResolver->addNamespace('*', null).
            //    This currently doesn't work because TYPO3 extends the "f" namespace, so we would need to partially
            //    ignore "f" as well, which is not possible with the current API. In TYPO3 context, again this leads to
            //    unresolvable ViewHelper exceptions which we can't intercept because 1.
            // 3. Pass the ViewHelperResolver from the current renderingContext to the method, along with its
            //    state (global namespaces) and special handling of ViewHelpers (possible DI implementations). This
            //    would pollute the interface with a seemingly irrelevant dependency. It also has the disadvantage
            //    that _all_ ViewHelper calls within the template would be resolved, including other components, which
            //    can lead to a chain of component templates being parsed. On top of that, it simply doesn't work
            //    for recursive component calls (infinite regress for recursive component definition).
            // 4. Use a custom ViewHelperResolver that only resolves select ViewHelpers necessary for the template
            //    structure and short-circuits all other ViewHelper calls.
            // Option 4 is currently the least intrusive variant and is implemented in TemplateStructureViewHelperResolver.
            // @todo the TemplateParser should be able to analyze the template structure in a first parsing pass,
            //       without resolving all other ViewHelpers in a template (with the described consequences).
            $renderingContext->setViewHelperResolver(new TemplateStructureViewHelperResolver());
            $parsedTemplate = $renderingContext->getTemplateParser()->parse(
                $this->getTemplatePaths()->getTemplateSource('Default', $templateName),
                $this->getTemplatePaths()->getTemplateIdentifier('Default', $templateName),
            );
            $this->componentDefinitionsCache[$viewHelperName] = new ComponentDefinition(
                $viewHelperName,
                $parsedTemplate->getArgumentDefinitions(),
                $this->additionalArgumentsAllowed($viewHelperName),
                $parsedTemplate->getAvailableSlots(),
            );
        }
        return $this->componentDefinitionsCache[$viewHelperName];
    }

    final public function getComponentRenderer(): ComponentRendererInterface
    {
        return new ComponentRenderer($this);
    }

    final public function resolveViewHelperClassName(string $viewHelperName): string
    {
        $expectedTemplateName = $this->resolveTemplateName($viewHelperName);
        if (!$this->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $expectedTemplateName)) {
            throw new UnresolvableViewHelperException(sprintf(
                'Based on your spelling, the system would load the component template "%s.%s" in "%s", however this file does not exist.',
                $expectedTemplateName,
                $this->getTemplatePaths()->getFormat(),
                implode(', ', $this->getTemplatePaths()->getTemplateRootPaths()),
            ), 1748511297);
        }
        return ComponentAdapter::class;
    }

    final public function getNamespace(): string
    {
        return static::class;
    }
}
