<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\View;

/**
 * Optional addition to ViewInterface if the view deals with template files.
 *
 * @api
 * @todo add return types with Fluid v5
 */
interface TemplateAwareViewInterface
{
    /**
     * @todo as the outfacing interface, this (and its implementations) should actually return a string
     * @param string $templateName A template name to render, e.g. "Main/Index"
     * @return mixed The rendered view
     * @api
     */
    public function render(string $templateName = '');
}
