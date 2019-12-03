<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * If content is empty use alternative text
 */
class OrViewHelper extends AbstractViewHelper
{
    /**
     * Initialize
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('content', 'mixed', 'Content to check if empty');
        $this->registerArgument('alternative', 'mixed', 'Alternative if content is empty');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string, using sprintf');
    }

    public function evaluate(RenderingContextInterface $renderingContext)
    {
        $arguments = $this->getArguments()->setRenderingContext($renderingContext)->getArrayCopy();
        $alternative = $arguments['alternative'];
        $variables = (array) $arguments['arguments'];

        $content = $arguments['content'] ?? $this->evaluateChildNodes($renderingContext);

        if (null === $content) {
            $content = $alternative;
        }

        if (!empty($content)) {
            $content = !empty($variables) ? vsprintf($content, $variables) : $content;
        }

        return $content;
    }
}
