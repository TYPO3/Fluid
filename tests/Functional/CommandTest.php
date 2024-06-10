<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional;

use TYPO3Fluid\Fluid\Tests\BaseTestCase;

final class CommandTest extends BaseTestCase
{
    public static function getCommandTestValues(): array
    {
        return [
            [
                '%s --help',
                'Use the CLI utility in the following modes',
                'Exception',
            ],
            [
                '%s help',
                'Supported commands:',
                'Exception',
            ],
            [
                'echo "Hello world!" | %s',
                'Hello world!',
                'Exeption',
            ],
            [
                'echo "Hello world!" | %s run',
                'Hello world!',
                'Exeption',
            ],
            [
                'echo "{foo}" | %s --variables "{\\"foo\\": \\"bar\\"}"',
                'bar',
                'Exception', 'foo',
            ],
            [
                'echo "{foo}" | %s run --variables "{\\"foo\\": \\"bar\\"}"',
                'bar',
                'Exception', 'foo',
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
