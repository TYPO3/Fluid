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
 * Interface ErrorHandlerInterface
 */
interface ErrorHandlerInterface
{
    /**
     * Handle errors caused by parsing templates, for example when
     * invalid arguments are used.
     *
     * @param Exception $error
     * @return string
     */
    public function handleParserError(Exception $error): string;

    /**
     * Handle errors caused by invalid expressions, e.g. errors
     * raised from misuse of `{variable xyz 123}` style expressions,
     * such as the casting expression `{variable as type}`.
     *
     * @param ExpressionException $error
     * @return string
     */
    public function handleExpressionError(ExpressionException $error): string;

    /**
     * Can be implemented to handle a ViewHelper errors which are
     * normally thrown from inside ViewHelpers during rendering.
     *
     * @param ViewHelperException $error
     * @return string
     */
    public function handleViewHelperError(ViewHelperException $error): string;

    /**
     * @param ViewException $error
     * @return string
     * @deprecated Will be removed in Fluid 4.0
     */
    public function handleViewError(ViewException $error): string;

}
