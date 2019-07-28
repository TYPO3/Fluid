<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends UnitTestCase
{

    /**
     * @dataProvider getFeatureStateTestValues
     * @param string $feature
     * @param mixed $state
     * @param bool $expected
     */
    public function testSetFeatureState(string $feature, $state, bool $expected)
    {
        $subject = new Configuration();
        $subject->setFeatureState($feature, $state);
        $this->assertSame($expected, $subject->isFeatureEnabled($feature));
    }

    public function getFeatureStateTestValues(): array
    {
        return [
            [
                Configuration::FEATURE_ESCAPING, 'false', false,
            ],
            [
                Configuration::FEATURE_ESCAPING, 'disabled', false,
            ],
            [
                Configuration::FEATURE_ESCAPING, '0', false,
            ],
            [
                Configuration::FEATURE_PARSING, 'true', true,
            ],
            [
                Configuration::FEATURE_PARSING, 'enabled', true,
            ],
            [
                Configuration::FEATURE_PARSING, '1', true,
            ],
            [
                Configuration::FEATURE_PARSING, true, true,
            ],
        ];
    }
}
