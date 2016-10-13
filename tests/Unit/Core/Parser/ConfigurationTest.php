<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends UnitTestCase
{

    /**
     * @test
     */
    public function testAddInterceptor()
    {
        $interceptor = new Escape();
        $configuration = new Configuration();
        $configuration->addInterceptor($interceptor);
        $interceptors = $configuration->getInterceptors(InterceptorInterface::INTERCEPT_OBJECTACCESSOR);
        $this->assertContains($interceptor, $interceptors);
    }

    /**
     * @test
     */
    public function testAddEscapingInterceptor()
    {
        $interceptor = new Escape();
        $configuration = new Configuration();
        $configuration->addEscapingInterceptor($interceptor);
        $interceptors = $configuration->getEscapingInterceptors(InterceptorInterface::INTERCEPT_OBJECTACCESSOR);
        $this->assertContains($interceptor, $interceptors);
    }
}
