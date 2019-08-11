<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3Fluid\Fluid\View\Exception as ViewException;

/**
 * Class TolerantErrorHandler
 *
 * Turns most frequently encountered types of exceptions into
 * friendlier output; swallows exceptions and returns a simple
 * string describing the problem.
 *
 * Useful in production - allows template to be rendered even
 * if part of the template or cascaded rendering causes errors.
 */
class TolerantErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param Exception $error
     * @return string
     */
    public function handleParserError(Exception $error): string
    {
        return 'Parser error: ' . $error->getMessage();
    }

    /**
     * @param ExpressionException $error
     * @return string
     */
    public function handleExpressionError(ExpressionException $error): string
    {
        return 'Invalid expression: ' . $error->getMessage();
    }

    /**
     * @param ViewHelperException $error
     * @return string
     */
    public function handleViewHelperError(ViewHelperException $error): string
    {
        return 'ViewHelper error: ' . $error->getMessage();
    }

    /**
     * @param ViewException $error
     * @return string
     * @deprecated Will be removed in Fluid 4.0
     */
    public function handleViewError(ViewException $error): string
    {
        return 'View error: ' . $error->getMessage();
    }
}
