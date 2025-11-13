<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Validation;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @internal
 */
final readonly class TemplateValidator
{
    /**
     * Collects deprecations and exceptions during parsing and compilation
     * of the supplied Fluid template files
     *
     * @param string[] $templates
     * @return TemplateValidatorResult[]
     */
    public function validateTemplateFiles(array $templates, ?RenderingContextInterface $baseRenderingContext = null): array
    {
        $baseRenderingContext ??= new RenderingContext();
        $results = [];
        foreach ($templates as $template) {
            $deprecations = $errors = [];
            set_error_handler(
                function (int $errno, string $errstr, string $errfile, int $errline) use (&$deprecations): bool {
                    $deprecations[] = new Deprecation($errfile, $errline, $errstr);
                    return true;
                },
                E_USER_DEPRECATED,
            );

            $renderingContext = clone $baseRenderingContext;
            $renderingContext->setViewHelperResolver($renderingContext->getViewHelperResolver()->getScopedCopy());
            $templatePaths = $renderingContext->getTemplatePaths();
            $templatePaths->setTemplatePathAndFilename($template);
            $templateIdentifier = $templatePaths->getTemplateIdentifier();
            $parsedTemplate = null;
            try {
                $parsedTemplate = $renderingContext->getTemplateParser()->parse(
                    $templatePaths->getTemplateSource(),
                    $templateIdentifier,
                );
            } catch (\Exception $e) {
                $errors[] = $e;
            }

            restore_error_handler();
            $results[$template] = new TemplateValidatorResult(
                $templateIdentifier,
                $template,
                $errors,
                $deprecations,
                $parsedTemplate,
            );
        }
        return $results;
    }
}
