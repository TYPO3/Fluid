<?php
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Exception thrown to stop the template compiling process
 * from compiling child nodes, without stopping the parent
 * compiling process from continuing.
 *
 * Used when ViewHelpers or template structures cause special
 * compiling of child nodes - in this use case the exception
 * is thrown after compiling the child nodes specially in the
 * ViewHelper, to instruct the compiler to skip to the next
 * same-level node instead of recursing into child nodes.
 *
 * Contains within it the intended replacement string which
 * is used by the TemplateCompiler when encountering this
 * Exception during ViewHelper compiling.
 *
 * For example implemented in Cache/StaticViewHelper of Fluid.
 *
 * @api
 */
class StopCompilingChildrenException extends \TYPO3Fluid\Fluid\Core\Exception
{

    /**
     * @var string
     */
    protected $replacementString;

    /**
     * @return string
     */
    public function getReplacementString()
    {
        return $this->replacementString;
    }

    /**
     * @param string $replacementString
     */
    public function setReplacementString($replacementString)
    {
        $this->replacementString = $replacementString;
    }
}
