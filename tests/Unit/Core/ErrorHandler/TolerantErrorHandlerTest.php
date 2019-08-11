<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ErrorHandler;

use TYPO3Fluid\Fluid\Core\ErrorHandler\TolerantErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Core\Parser\ExpressionException;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

class TolerantErrorHandlerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function handleParserErrorReturnsStringMessage(): void
    {
        $error = new Exception('foo');
        $subject = new TolerantErrorHandler();
        $this->assertSame('Parser error: foo', $subject->handleParserError($error));
    }

    /**
     * @test
     */
    public function handleViewHelperErrorReturnsStringMessage(): void
    {
        $error = new \TYPO3Fluid\Fluid\Core\ViewHelper\Exception('foo');
        $subject = new TolerantErrorHandler();
        $this->assertSame('ViewHelper error: foo', $subject->handleViewHelperError($error));
    }

    /**
     * @test
     */
    public function handleViewErrorReturnsStringMessage(): void
    {
        $error = new \TYPO3Fluid\Fluid\View\Exception('foo');
        $subject = new TolerantErrorHandler();
        $this->assertSame('View error: foo', $subject->handleViewError($error));
    }

    /**
     * @test
     */
    public function handleExpressionErrorReturnsStringMessage(): void
    {
        $error = new ExpressionException('foo');
        $subject = new TolerantErrorHandler();
        $this->assertSame('Invalid expression: foo', $subject->handleExpressionError($error));
    }
}
