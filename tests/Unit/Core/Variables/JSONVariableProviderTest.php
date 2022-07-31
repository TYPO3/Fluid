<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\JSONVariableProvider;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;

class JSONVariableProviderTest extends AbstractFunctionalTestCase
{
    public function provideVariablesDataProvider(): array
    {
        return [
            ['{}', []],
            ['{"foo": "bar"}', ['foo' => 'bar']],
            [__DIR__ . '/Fixtures/test.json', ['foo' => 'bar']],
        ];
    }

    /**
     * @test
     * @dataProvider provideVariablesDataProvider
     */
    public function provideVariables(string $input, array $expected): void
    {
        $provider = new JSONVariableProvider();
        $provider->setSource($input);
        self::assertEquals($input, $provider->getSource());
        self::assertEquals($expected, $provider->getAll());
        self::assertEquals(array_keys($expected), $provider->getAllIdentifiers());
        foreach ($expected as $key => $value) {
            self::assertEquals($value, $provider->get($key));
        }
    }
}
