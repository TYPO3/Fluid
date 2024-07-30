<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor;

use TYPO3Fluid\Fluid\Core\Parser\PassthroughSourceException;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * This template processor has a single purpose:
 *
 * When a template file contains the string `{parsing off}`, the processor
 * throws an Exception instructing the parser to display the source instead
 * of attempting to parse the template source.
 *
 * Doing so completely disables the parser and template splitting since it
 * happens before the parser receives the source.
 */
class PassthroughSourceModifierTemplateProcessor implements TemplateProcessorInterface
{
    /**
     * @throws PassthroughSourceException
     */
    public function preProcessSource(string $templateSource): string
    {
        if (strpos($templateSource, '{parsing off}') !== false) {
            $templateSource = str_replace('{parsing off}', '', $templateSource);
            $stopException = new PassthroughSourceException();
            $stopException->setSource($templateSource);
            throw $stopException;
        }
        return str_replace('{parsing on}', '', $templateSource);
    }

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        // void
    }
}
