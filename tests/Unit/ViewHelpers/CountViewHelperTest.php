<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ArrayNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\CountViewHelper;

/**
 * Testcase for CountViewHelper
 */
class CountViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     */
    public function throwsExceptionOnIncompatibleSubject(): void
    {
        $context = new RenderingContextFixture();
        $viewHelper = new CountViewHelper();
        $subject = new CountViewHelper();
        $this->setExpectedException(Exception::class);
        $viewHelper->getArguments()['subject'] = $subject;
        $viewHelper->evaluate($context);
    }

    public function getStandardTestValues(): array
    {
        $context = new RenderingContextFixture();
        return [
            'returns zero if subject is null' => [0, $context, ['subject' => null]],
            'returns count of simple array passed as argument' => [3, $context, ['subject' => ['foo', 'bar', 'baz']]],
            'returns count of simple array passed as child node' => [3, $context, null, [new ArrayNode(['foo', 'bar', 'baz'])]],
            'returns count of simple iterator passed as argument' => [3, $context, ['subject' => new \ArrayIterator(['foo', 'bar', 'baz'])]],
        ];
    }
}
