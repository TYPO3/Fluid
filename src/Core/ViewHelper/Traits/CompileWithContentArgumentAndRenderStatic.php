<?php
namespace TYPO3Fluid\Fluid\Core\ViewHelper\Traits;

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Compiler\ViewHelperCompiler;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;

/**
 * Class CompilableWithContentArgumentAndRenderStatic
 *
 * Provides default methods for rendering and compiling
 * any ViewHelper that conforms to the `renderStatic`
 * method pattern but has the added common use case that
 * an argument value must be checked and used instead of
 * the normal render children closure, if that named
 * argument is specified and not empty.
 *
 * @deprecated Trait no longer required, content argument supported by any VH via base class.
 */
trait CompileWithContentArgumentAndRenderStatic
{
    /**
     * @return string
     */
    protected function resolveContentArgumentName()
    {
        if (empty($this->contentArgumentName)) {
            $registeredArguments = call_user_func_array([$this, 'prepareArguments'], []);
            foreach ($registeredArguments as $registeredArgument) {
                if (!$registeredArgument->isRequired()) {
                    $this->contentArgumentName = $registeredArgument->getName();
                    return $this->contentArgumentName;
                }
            }
            throw new Exception(
                sprintf('Attempting to compile %s failed. Chosen compile method requires that ViewHelper has ' .
                'at least one registered and optional argument', __CLASS__)
            );
        }
        return $this->contentArgumentName;
    }
}
