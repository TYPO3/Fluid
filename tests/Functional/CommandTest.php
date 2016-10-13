<?php
namespace FluidTYPO3Fluid\Flux\Tests\Unit\Functional;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use org\bovigo\vfs\vfsStream;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;

/**
 * Class CommandTest
 */
class CommandTest extends BaseTestCase
{

    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        vfsStream::setup('fakecache/');
    }

    /**
     * @param string $argumentString
     * @param array $mustContain
     * @param array $mustNotContain
     * @dataProvider getCommandTestValues
     */
    public function testCommand($argumentString, array $mustContain, array $mustNotContain)
    {
        $bin = realpath(__DIR__ . '/../../bin/fluid');
        $command = sprintf($argumentString, $bin);
        $output = shell_exec($command);
        foreach ($mustContain as $mustContainString) {
            $this->assertContains($mustContainString, $output);
        }
        foreach ($mustNotContain as $mustNotContainString) {
            $this->assertNotContains($mustNotContainString, $output);
        }
    }

    /**
     * @return array
     */
    public function getCommandTestValues()
    {
        $dummyVariablesFile = realpath(__DIR__ . '/Fixtures/Variables/DummyVariables.json');
        return [
            ['%s --help', ['Use the CLI utility in the following modes'], ['Exception']],
            ['echo "Hello world!" | %s', ['Hello world!'], ['Exeption']],
            ['echo "{foo}" | %s --variables "{\\"foo\\": \\"bar\\"}"', ['bar'], ['Exception', 'foo']],
        ];
    }
}
