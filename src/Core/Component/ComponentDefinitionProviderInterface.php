<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Component;

/**
 * The ComponentDefinitionProviderInterface provides the necessary information to Fluid to
 * adapt a ViewHelper call in a template (e. g. <my:custom.element />) to a rendered component.
 * The adaption is performed by the ComponentAdapter.
 *
 * @api
 * @see AbstractComponentCollection
 */
interface ComponentDefinitionProviderInterface
{
    /**
     * Returns the component definition for the specified ViewHelper name. This enables
     * Fluid to pre-validate the component's arguments in the parsing step and to perform
     * the correct escaping of the supplied arguments.
     *
     * @param string $viewHelperName ViewHelper tag name from a template, e. g. atom.button
     */
    public function getComponentDefinition(string $viewHelperName): ComponentDefinition;

    /**
     * Returns an instance of the component renderer that should be used to render the
     * provided components.
     */
    public function getComponentRenderer(): ComponentRendererInterface;
}
