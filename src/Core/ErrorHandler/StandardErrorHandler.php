<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Core\Parser\SequencingException;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3Fluid\Fluid\View\Exception as ViewException;

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
        $class = get_class($error);
        $message = $error->getMessage();
        if ($error instanceof SequencingException) {
            $message .= ' - ' . $error->getExcerpt();
        }
        $replacement = new $class($message);
        throw $replacement;
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
     * @param ViewHelperException $error
     * @return void
     * @throws ViewHelperException
     */
    public function handleViewHelperError(ViewHelperException $error): string
    {
        throw $error;
    }

    /**
     * @param ViewException $error
     * @return void
     * @throws ViewException
     * @deprecated Will be removed in Fluid 4.0
     */
    public function handleViewError(ViewException $error): string
    {
        throw $error;
    }

}
