<?php

declare(strict_types=1);
namespace TYPO3Fluid\Fluid\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Optional addition to ViewInterface if the view deals with template files.
 *
 * @api
 */
interface TemplateAwareViewInterface
{
    /**
     * @param string A template name to render, e.g. "Main/Index"
     * @return string The rendered view
     * @api
     */
    public function render(string $templateName = '');
}
