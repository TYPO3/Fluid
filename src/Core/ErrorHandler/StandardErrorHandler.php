<?php
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
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
     * @param Exception $error
     * @throws Exception
     * @return void
     */
    public function handleParserError(Exception $error): string
    {
        throw $error;
    }

    /**
     * @param ExpressionException $error
     * @throws ExpressionException
     * @return void
     */
    public function handleExpressionError(ExpressionException $error): string
    {
        throw $error;
    }

    /**
     * @param \TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return void
     */
    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error): string
    {
        throw $error;
    }

    /**
     * @param StopCompilingException $error
     * @return string
     */
    public function handleCompilerError(StopCompilingException $error): string
    {
        return '';
    }

    /**
     * @param \TYPO3Fluid\Fluid\View\Exception $error
     * @throws \TYPO3Fluid\Fluid\View\Exception
     * @return void
     */
    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error): string
    {
        throw $error;
    }

}
