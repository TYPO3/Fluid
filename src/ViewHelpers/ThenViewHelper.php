<?php
namespace TYPO3Fluid\Fluid\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileEmpty;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\PassthroughRenderChildren;

/**
 * "THEN" -> only has an effect inside of "IF". See If-ViewHelper for documentation.
 *
 * @see \TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper
 * @api
 */
class ThenViewHelper extends AbstractViewHelper
{
    use CompileEmpty;
    use PassthroughRenderChildren;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;
}
