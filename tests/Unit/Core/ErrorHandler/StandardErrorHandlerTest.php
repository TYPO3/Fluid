<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ErrorHandler;

use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class StandardErrorHandlerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function handleParserErrorRethrowsException(): void
    {
        $error = new Exception('foo');
        $subject = new StandardErrorHandler();
        $this->setExpectedException(Exception::class);
        $subject->handleParserError($error);
    }

    /**
     * @test
     */
    public function handleViewHelperErrorRethrowsException(): void
    {
        $error = new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception('foo');
        $subject = new StandardErrorHandler();
        $this->setExpectedException(\TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class);
        $subject->handleViewHelperError($error);
    }

    /**
     * @test
     */
    public function handleViewErrorRethrowsException(): void
    {
        $error = new \TYPO3Fluid\Fluid\View\Exception('foo');
        $subject = new StandardErrorHandler();
        $this->setExpectedException(\TYPO3Fluid\Fluid\View\Exception::class);
        $subject->handleViewError($error);
    }

    /**
     * @test
     */
    public function handleExpressionErrorRethrowsException(): void
    {
        $error = new ExpressionException('foo');
        $subject = new StandardErrorHandler();
        $this->setExpectedException(ExpressionException::class);
        $subject->handleExpressionError($error);
    }
}
