<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tests\BaseTestCase;

/**
 * Class CommandTest
 */
class CommandTest extends BaseTestCase
{
    /**
     * @param string $argumentString
     * @param array $mustContain
     * @param array $mustNotContain
     * @dataProvider getCommandTestValues
     */
    public function testCommand(string $argumentString, array $mustContain, array $mustNotContain): void
    {
        $bin = realpath(__DIR__ . '/../../bin/fluid');
        $command = sprintf($argumentString, $bin);
        $output = shell_exec($command);
        foreach ($mustContain as $mustContainString) {
            $this->assertStringContainsString($mustContainString, $output);
        }
        foreach ($mustNotContain as $mustNotContainString) {
            $this->assertStringNotContainsString($mustNotContainString, $output);
        }
    }

    /**
     * @return array
     */
    public function getCommandTestValues(): array
    {
        return [
            ['%s --help', ['Use the CLI utility in the following modes'], [\Exception::class]],
            ['echo "Hello world!" | %s', ['Hello world!'], ['Exeption']],
            ['echo "{foo}" | %s --variables "{\\"foo\\": \\"bar\\"}"', ['bar'], [\Exception::class, 'foo']],
        ];
    }
}
