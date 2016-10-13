<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Compiler\ViewHelperCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

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
     * @return string Rendered string
     * @api
     */
    public function render()
    {
        return self::renderStatic(
            $this->arguments,
            call_user_func_array([$this, 'buildRenderChildrenClosure'], []),
            $this->renderingContext
        );
    }

    /**
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
        list ($initialization, $execution) = ViewHelperCompiler::getInstance()->compileWithCallToStaticMethod(
            $this,
            $argumentsName,
            $closureName
        );
        $initializationPhpCode .= $initialization;
        return $execution;
    }
}
