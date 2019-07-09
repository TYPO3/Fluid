<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Compiler\ViewHelperCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Class CompilableWithRenderStatic
 *
 * Provides default methods for rendering and compiling
 * any ViewHelper that conforms to the `renderStatic`
 * method pattern.
 */
trait CompileWithRenderStatic
{

    /**
     * Default render method - simply calls renderStatic() with a
     * prepared set of arguments.
     *
     * @return mixed Rendered result
     * @api
     */
    public function render()
    {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @return \Closure
     */
    protected abstract function buildRenderChildrenClosure();

    /**
     * @param string $argumentsName
     * @param string $closureName
     * @param string $initializationPhpCode
     * @param ViewHelperNode $node
     * @param TemplateCompiler $compiler
     * @return string
     */
    public function compile(
        string $argumentsName,
        string $closureName,
        string &$initializationPhpCode,
        ViewHelperNode $node,
        TemplateCompiler $compiler
    ) {
        list ($initialization, $execution) = ViewHelperCompiler::getInstance()->compileWithCallToStaticMethod(
            $this,
            $argumentsName,
            $closureName
        );
        $initializationPhpCode .= $initialization;
        return $execution;
    }
}
