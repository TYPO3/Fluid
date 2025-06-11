<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * Internal interface that is used to provide stripped down information to the default
 * ComponentRenderer implementation. This allows for a structured way to provide only the
 * necessary information to render a Fluid-based component template, while still allowing
 * more flexibility by setting a different renderer altogether, which doesn't need to
 * use this interface (e. g. if it uses a different templating engine internally).
 *
 * @internal This interface should only be used for type hinting
 * @see AbstractComponentCollection
 */
interface ComponentTemplateResolverInterface
{
    /**
     * Supplies the TemplatePaths instance which will be used to resolve component templates.
     * Usually, one or more templateRootPaths are defined which contain the component templates
     */
    public function getTemplatePaths(): TemplatePaths;

    /**
     * Provides additional variables to component views
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return array<string, mixed>
     */
    public function getAdditionalVariables(string $viewHelperName): array;

    /**
     * Creates the path to a template from a ViewHelper name in a template.
     *
     * @param string $viewHelperName  ViewHelper tag name from a template, e. g. atom.button
     * @return string                 Component template name to be used for this ViewHelper,
     *                                without format suffix, e. g. Atom/Button
     */
    public function resolveTemplateName(string $viewHelperName): string;
}
