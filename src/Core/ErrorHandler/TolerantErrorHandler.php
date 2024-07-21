<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

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
    public function handleParserError(\TYPO3Fluid\Fluid\Core\Parser\Exception $error): string
    {
        return 'Parser error: ' . $error->getMessage() . ' Offending code: ';
    }

    public function handleExpressionError(\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException $error): string
    {
        return 'Invalid expression: ' . $error->getMessage();
    }

    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error): string
    {
        return 'ViewHelper error: ' . $error->getMessage() . ' - Offending code: ';
    }

    public function handleCompilerError(\TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error): string
    {
        return '';
    }

    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error): string
    {
        if ($error instanceof \TYPO3Fluid\Fluid\View\Exception\InvalidSectionException) {
            return 'Section rendering error: ' . $error->getMessage() . ' Section rendering is mandatory; "optional" is false.';
        }
        return 'View error: ' . $error->getMessage();
    }
}
