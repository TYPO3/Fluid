<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Hides, but still executes, the tag content.
 *
 * Useful when you have Fluid code that you want to execute
 * but do not wish to output. For example, around variable
 * assignments to remove resulting whitespace.
 *
 * = Examples =
 *
 * <code title="Hiding output">
 * <f:format.hide>
 *      <!-- Everything inside the tag is executed but not output -->
 *      <!--
 *          Which menas that among other things, you can use HTML
 *          comments which will not be output but are visible to
 *          developers reading the template source code.
 *      -->
 *      {string -> f:format.htmlspecialchars() -> f:variable(name: 'newvariable')}
 * </f:format.hide>
 * </code>
 * <output>
 * (Content of {string} without any conversion/escaping)
 * </output>
 *
 * @api
 */
class HideViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $renderChildrenClosure();
    }

}
