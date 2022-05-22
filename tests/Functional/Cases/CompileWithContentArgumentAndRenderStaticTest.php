<?php

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

class CompileWithContentArgumentAndRenderStaticTest extends BaseFunctionalTestCase
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

    public function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            // with trait but without contentArgumentProperty set in viewhelper and having optional argument
            'children content but no argument value' => [
                '<test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>',
                [],
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
                [],
                null,
                true
            ],
            'children content and argument value' => [
                '<test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren firstOptionalArgument="firstOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticUseFirstRegisteredOptionalArgumentAsRenderChildren>',
                [],
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"renderChildrenClosure": "firstOptionalArgument"',
                ],
                [],
                null,
                true
            ],
            // with trait but without contentArgumentProperty set in viewhelper and having first optional argument as second argument
            'children content but no argument value [optional is second argument]' => [
                '<test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren requiredArgument="dummy">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren>',
                [],
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
                [],
                null,
                true
            ],
            'children content and argument value [optional is second argument]' => [
                '<test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren requiredArgument="dummy" firstOptionalArgument="firstOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticFirstRegisteredOptionalArgumentAfterRequiredArgumentAsRenderChildren>',
                [],
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"renderChildrenClosure": "firstOptionalArgument"',
                ],
                [],
                null,
                true
            ],
            // now the hard cases - setting the contentArgumentName property through the constructor
            'children content but no argument value [use second optional argument][explicit set in __construct]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>',
                [],
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"arguments[secondOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
                [],
                null,
                true
            ],
            'children content and argument value [use second optional argument][explicit set in __construct]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor firstOptionalArgument="firstOptionalArgument" secondOptionalArgument="secondOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentInConstructor>',
                [],
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"arguments[secondOptionalArgument]": "secondOptionalArgument"',
                    '"renderChildrenClosure": "secondOptionalArgument"',
                ],
                [],
                null,
                true
            ],
            // now the hard cases - setting the contentArgumentName property through overriding resolveContentArgumentName
            'children content but no argument value [use second optional argument][explicit set in overriden resolveContentArgumentName]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>',
                [],
                [
                    '"arguments[firstOptionalArgument]": null',
                    '"arguments[secondOptionalArgument]": null',
                    '"renderChildrenClosure": "mustBeRenderingChildrenClosure"',
                ],
                [],
                null,
                true
            ],
            'children content and argument value [use second optional argument][explicit set in overriden resolveContentArgumentName]' => [
                '<test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod firstOptionalArgument="firstOptionalArgument" secondOptionalArgument="secondOptionalArgument">mustBeRenderingChildrenClosure</test:compileWithContentArgumentAndRenderStaticExplicitSetArgumentNameForContentOverriddenResolveContentArgumentNameMethod>',
                [],
                [
                    '"arguments[firstOptionalArgument]": "firstOptionalArgument"',
                    '"arguments[secondOptionalArgument]": "secondOptionalArgument"',
                    '"renderChildrenClosure": "secondOptionalArgument"',
                ],
                [],
                null,
                true
            ],
        ];
    }

    /**
     * Perform a standard test on the source or stream provided,
     * rendering it with $variables assigned and checking the
     * output for presense of $expected values and confirming
     * that none of the $notExpected values are present.
     *
     * Same as testTemplateCodeFixture() but includes a cache
     * in the tests. Silently skipped if the test case does not
     * return a valid cache.
     *
     * @param string|resource $sourceOrStream
     * @param array $variables
     * @param array $expected
     * @param array $notExpected
     * @param string|null $expectedException
     * @test
     * @dataProvider getTemplateCodeFixturesAndExpectations
     */
    public function testTemplateCodeFixtureWithCache($sourceOrStream, array $variables, array $expected, array $notExpected, $expectedException = null)
    {
        if ($this->getCache()) {
            $this->testTemplateCodeFixture($sourceOrStream, $variables, $expected, $notExpected, $expectedException, true);
            $this->testTemplateCodeFixture($sourceOrStream, $variables, $expected, $notExpected, $expectedException, true);
        } else {
            self::markTestSkipped('Cache-specific test skipped');
        }
    }

    /**
     * Perform a standard test on the source or stream provided,
     * rendering it with $variables assigned and checking the
     * output for presense of $expected values and confirming
     * that none of the $notExpected values are present.
     *
     * Same as testTemplateCodeFixture() but includes a cache
     * in the tests. Silently skipped if the test case does not
     * return a valid cache.
     *
     * @param string|resource $sourceOrStream
     * @param array $variables
     * @param array $expected
     * @param array $notExpected
     * @param string|null $expectedException
     * @test
     * @dataProvider getTemplateCodeFixturesAndExpectations
     */
    public function testTemplateCodeFixtureWithoutCache($sourceOrStream, array $variables, array $expected, array $notExpected, $expectedException = null)
    {
        $this->testTemplateCodeFixture($sourceOrStream, $variables, $expected, $notExpected, $expectedException, false);
        $this->testTemplateCodeFixture($sourceOrStream, $variables, $expected, $notExpected, $expectedException, false);
    }
}
