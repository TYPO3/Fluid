<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * If content is empty use alternative text
 */
class OrViewHelper extends AbstractViewHelper
{

    use CompileWithContentArgumentAndRenderStatic;

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

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $alternative = $arguments['alternative'];
        $arguments = (array) $arguments['arguments'];

        if (empty($arguments)) {
            $arguments = null;
        }

        $content = $renderChildrenClosure();

        if (null === $content) {
            $content = $alternative;
        }

        if (false === empty($content)) {
            $content = null !== $arguments ? vsprintf($content, $arguments) : $content;
        }

        return $content;
    }
}
