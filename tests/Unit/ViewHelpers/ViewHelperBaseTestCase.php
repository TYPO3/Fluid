<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use PHPUnit\Framework\Constraint\IsAnything;
use TYPO3Fluid\Fluid\Component\ComponentInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Base test class for testing view helpers
 */
abstract class ViewHelperBaseTestCase extends UnitTestCase
{
    /**
     * @test
     * @dataProvider getStandardTestValues
     * @param mixed $expectedOutput
     * @param RenderingContextInterface $renderingContext
     * @param array|null $arguments
     * @param array|null $children
     * @param int $numberOfExecutions
     */
    public function runStandardTests(
        $expectedOutput,
        ?RenderingContextInterface $renderingContext = null,
        ?array $arguments = null,
        ?array $children = null,
        int $numberOfExecutions = 1
    ): void {
        $renderingContext = $renderingContext ?? new RenderingContextFixture();
        $viewHelperClassName = str_replace('\\Tests\\Unit', '', substr(static::class, 0, -4));
        /** @var ComponentInterface $viewHelper */
        $viewHelper = new $viewHelperClassName();
        $viewHelper->getArguments()->setRenderingContext($renderingContext)->assignAll($arguments ?? []);
        $viewHelper->onOpen($renderingContext);
        foreach ($children ?? [] as $child) {
            $viewHelper->addChild($child);
        }
        $viewHelper->onClose($renderingContext);

        if ($numberOfExecutions > 1) {
            $output = null;
            while (--$numberOfExecutions >= 0) {
                $output .= $viewHelper->evaluate($renderingContext);
            }
        } else {
            $output = $viewHelper->evaluate($renderingContext);
        }

        if ($expectedOutput instanceof IsAnything) {
            // Semi-void case: redundant assertion to prevent an error; if the tested logic did not break that's consider a pass
            $this->assertTrue(true);
        } else {
            $this->assertSame($expectedOutput, $output);
        }
    }

    abstract public function getStandardTestValues(): array;
}
