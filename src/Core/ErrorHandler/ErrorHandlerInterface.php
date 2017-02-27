<?php
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

/**
 * Interface ErrorHandlerInterface
 */
interface ErrorHandlerInterface
{
    /**
     * Handle errors caused by parsing templates, for example when
     * invalid arguments are used.
     *
     * @param \TYPO3Fluid\Fluid\Core\Parser\Exception $error
     * @return string
     */
    public function handleParserError(\TYPO3Fluid\Fluid\Core\Parser\Exception $error);

    /**
     * Handle errors caused by invalid expressions, e.g. errors
     * raised from misuse of `{variable xyz 123}` style expressions,
     * such as the casting expression `{variable as type}`.
     *
     * @param \TYPO3Fluid\Fluid\Core\Parser\ExpressionException $error
     * @return string
     */
    public function handleExpressionError(\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException $error);

    /**
     * Can be implemented to handle a ViewHelper errors which are
     * normally thrown from inside ViewHelpers during rendering.
     *
     * @param \TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error
     * @return string
     */
    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error);

    /**
     * Can be implemented to handle "cannot compile" errors in
     * desired ways (normally this simply disables the compiling,
     * but if your application deems compiler errors fatal then
     * you can throw a different exception type here).
     *
     * @param \TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error
     * @return string
     */
    public function handleCompilerError(\TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error);

    /**
     * @param \TYPO3Fluid\Fluid\View\Exception $error
     * @return string
     */
    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error);

}
