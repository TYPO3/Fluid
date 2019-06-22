<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Class ParserRuntimeOnly
 */
trait ParserRuntimeOnly
{
    /**
     * @return null
     */
    public function render()
    {
        return null;
    }

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string|null
     */
    public function compile(string $argumentsName, string $closureName, string &$initializationPhpCode, ViewHelperNode $node, TemplateCompiler $compiler): ?string
    {
        return null;
    }
}