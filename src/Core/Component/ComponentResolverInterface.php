<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * @internal This interface should only be used for type hinting
 * @see AbstractComponentCollection
 */
interface ComponentResolverInterface
{
    /**
     * Supplies the TemplatePaths instance which will be used to resolve component templates.
     * Usually, one or more templateRootPaths are defined which contain the component templates
     */
    public function getTemplatePaths(): TemplatePaths;

    /**
     * Provides additional variables to component views
     *
     * @return array<string, mixed>
     */
    public function getAdditionalVariables(): array;

    /**
     * Creates the path to a template from a ViewHelper name in a template.
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return string                 Component template name to be used for this ViewHelper,
     *                                without format suffix, e. g. Atom/Button
     */
    public function resolveTemplateName(string $viewHelperName): string;

    /**
     * Returns the argument definitions for the specified ViewHelper name.
     * This can also be used to define default argument definitions that should be present in
     * all components.
     *
     * @todo For now, the ViewHelperResolver instance is necessary. Once this parser dependency
     *       is resolved, this definition could be moved to the ViewHelperResolverDelegateInterface
     * @param string $viewHelperName             ViewHelper tag name from a template, e. g. atom.button
     * @return array<string, ArgumentDefinition>
     */
    public function getArgumentDefinitions(string $viewHelperName, ViewHelperResolver $viewHelperResolver): array;
}
