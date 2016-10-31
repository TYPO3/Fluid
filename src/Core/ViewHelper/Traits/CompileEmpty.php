<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;


use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Class CompileEmpty
 *
 * Implemented by ViewHelpers which must compile to provide empty
 * content (e.g. ViewHelper is a structure ViewHelper which uses
 * postParseEvent and/or are not supposed to render once compiled).
 */
trait CompileEmpty
{
    /**
     * Return empty string to be placed in the compiled template.
     *
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile(
        $argumentsName,
        $closureName,
        &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        return '\'\'';
    }
}
