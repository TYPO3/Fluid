<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional;

use TYPO3Fluid\Fluid\Tests\BaseTestCase;

class CommandTest extends BaseTestCase
{
    public function getCommandTestValues(): array
    {
        return [
            [
                '%s --help',
                'Use the CLI utility in the following modes',
                'Exception'
            ],
            [
                'echo "Hello world!" | %s',
                'Hello world!',
                'Exeption'
            ],
            [
                'echo "{foo}" | %s --variables "{\\"foo\\": \\"bar\\"}"',
                'bar',
                'Exception', 'foo'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getCommandTestValues
     */
    public function command(string $argumentString, string $mustContainString, string $mustNotContainString): void
    {
        $bin = realpath(__DIR__ . '/../../bin/fluid');
        $command = sprintf($argumentString, $bin);
        $output = shell_exec($command);
        self::assertStringContainsString($mustContainString, $output);
        self::assertStringNotContainsString($mustNotContainString, $output);
    }
}
