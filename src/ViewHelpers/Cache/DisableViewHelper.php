<?php
namespace TYPO3Fluid\Fluid\ViewHelpers\Cache;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to disable template compiling
 *
 * Inserting this ViewHelper at any point in the template,
 * including inside conditions which do not get rendered,
 * will forcibly disable the caching/compiling of the full
 * template file to a PHP class.
 *
 * Use this if for whatever reason your platform is unable
 * to create or load PHP classes (for example on read-only
 * file systems or when using an incompatible default cache
 * backend).
 *
 * Passes through anything you place inside the ViewHelper,
 * so can safely be used as container tag, as self-closing
 * or with inline syntax - all with the same result.
 *
 * = Examples =
 *
 * <code title="Self-closing">
 * <f:cache.disable />
 * </code>
 *
 * <code title="Inline mode">
 * {f:cache.disable()}
 * </code>
 *
 * <code title="Container tag">
 * <f:cache.disable>
 *    Some output or Fluid code
 * </f:cache.disble>
 * Additional output is also not compilable because of the ViewHelper
 * </code>
 *
 * @api
 */
class DisableViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return string
     */
    public function render()
    {
        return $this->renderChildren();
    }

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        $compiler->disable();
    }
}
