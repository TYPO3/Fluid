<?php
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Class StandardErrorHandler
 *
 * Implements the default type of error handling for
 * Fluid, which means all exceptions are thrown except
 * for the StopCompilingException which is tolerated
 * (as a means to forcibly disable caching).
 */
class StandardErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param \TYPO3Fluid\Fluid\Core\Parser\Exception $error
     * @throws \TYPO3Fluid\Fluid\Core\Parser\Exception
     * @return void
     */
    public function handleParserError(\TYPO3Fluid\Fluid\Core\Parser\Exception $error)
    {
        throw $error;
    }

    /**
     * @param \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException $error
     * @throws \TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException
     * @return void
     */
    public function handleExpressionError(\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException $error)
    {
        throw $error;
    }

    /**
     * @param \TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return void
     */
    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error)
    {
        throw $error;
    }

    /**
     * @param \TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error
     * @return void
     */
    public function handleCompilerError(\TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error)
    {
    }

    /**
     * @param \TYPO3Fluid\Fluid\View\Exception $error
     * @throws \TYPO3Fluid\Fluid\View\Exception
     * @return void
     */
    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error)
    {
        throw $error;
    }

}
