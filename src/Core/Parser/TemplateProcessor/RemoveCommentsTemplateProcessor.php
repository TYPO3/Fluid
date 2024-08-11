<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor;

use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessorInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RemoveCommentsTemplateProcessor implements TemplateProcessorInterface
{
    protected RenderingContextInterface $renderingContext;

    public function setRenderingContext(RenderingContextInterface $renderingContext): void
    {
        $this->renderingContext = $renderingContext;
    }

    /**
     * Replaces all comment ViewHelpers with empty lines to exclude it
     * from further processing in the templateParser while maintaining
     * the line-count of the template string for the exception handler
     * to reference to. These empty lines are again wrapped inside
     * <f:comment> to not introduce any unwanted whitespace changes in
     * existing templates.
     */
    public function preProcessSource(string $templateSource): string
    {
        $parts = preg_split('#(</?f:comment>)#', $templateSource, -1, PREG_SPLIT_DELIM_CAPTURE);

        $balance = 0;
        foreach ($parts as $index => $part) {
            if ($part === '<f:comment>') {
                $balance++;
            }
            if ($balance > 0) {
                $parts[$index] = '<f:comment>' . str_repeat(PHP_EOL, substr_count($part, PHP_EOL)) . '</f:comment>';
            }
            if ($part === '</f:comment>') {
                $balance--;
            }
        }

        return implode('', $parts);
    }
}
