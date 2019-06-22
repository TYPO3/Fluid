<?php
namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException;
use TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException;
use TYPO3Fluid\Fluid\View\Exception\InvalidSectionException;
/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
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
        return 'Parser error: ' . $error->getMessage() . ' Offending code: ';
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
     * @param \TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error
     * @return string
     */
    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error): string
    {
        return 'ViewHelper error: ' . $error->getMessage() . ' - Offending code: ';
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
     * @return string
     */
    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error): string
    {
        if ($error instanceof InvalidSectionException) {
            return 'Section rendering error: ' . $error->getMessage() . ' Section rendering is mandatory; "optional" is false.';
        }
        return 'View error: ' . $error->getMessage();
    }
}
