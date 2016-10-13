<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class SwitchTest
 */
class SwitchTest extends BaseFunctionalTestCase
{

    /**
     * If your test case requires a cache, override this
     * method and return an instance.
     *
     * @return FluidCacheInterface
     */
    protected function getCache()
    {
        return new SimpleFileCache(sys_get_temp_dir());
    }

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        return [
            'Ignores whitespace inside parent switch outside case children' => [
                '<f:switch expression="1">   <f:case value="2">NO</f:case>   <f:case value="1">YES</f:case>   </f:switch>',
                [],
                [],
                ['   ']
            ],
            'Ignores text inside parent switch outside case children' => [
                '<f:switch expression="1">TEXT<f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                [],
                [],
                ['TEXT']
            ],
            'Ignores text and whitespace inside parent switch outside case children' => [
                '<f:switch expression="1">   TEXT   <f:case value="2">NO</f:case><f:case value="1">YES</f:case></f:switch>',
                [],
                [],
                ['TEXT', '   ']
            ],
        ];
    }
}
