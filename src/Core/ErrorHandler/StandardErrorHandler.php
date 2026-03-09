<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\ErrorHandler;

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
    public function handleParserError(\TYPO3Fluid\Fluid\Core\Parser\Exception $error): string
    {
        throw $error;
    }

    public function handleExpressionError(\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\ExpressionException $error): string
    {
        throw $error;
    }

    public function handleViewHelperError(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception $error, ?string $originalTemplatePath = null): string
    {
        if ($originalTemplatePath !== null) {
            throw new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception(sprintf(
                '%s in %s: %s (%s:%d)',
                get_class($error),
                $originalTemplatePath,
                $error->getMessage(),
                $error->getFile(),
                $error->getLine(),
            ), $error->getCode(), $error);
        }
        throw $error;
    }

    public function handleCompilerError(\TYPO3Fluid\Fluid\Core\Compiler\StopCompilingException $error): string
    {
        return '';
    }

    public function handleViewError(\TYPO3Fluid\Fluid\View\Exception $error): string
    {
        throw $error;
    }
}
