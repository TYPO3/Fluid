<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering;

use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Cache\SimpleFileCache;
use TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

class DataAccessorTest extends UnitTestCase
{
    /**
     * @return array
     */
    public function dataIsRenderedDataProvider()
    {
        return [
            // array values
            0 => [
                ['value' => 'value'],
                ['value' => 'value'],
            ],
            1 => [
                new \ArrayObject(['value' => 'value']),
                ['value' => 'value'],
            ],
            // accessing using properties directly
            2 => [
                $this->createObjectWithProperties(),
                [
                    'privateValue' => null,
                ],
                'Cannot access private property TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects\WithProperties::$privateValue',
            ],
            3 => [
                $this->createObjectWithProperties(),
                [
                    'publicValue' => 'publicValue',
                ],
            ],
            // accessing using camelCased getters
            4 => [
                $this->createObjectWithCamelCaseGetter(),
                [
                    'privateValue' => 'privateValue@getPrivateValue()',
                    'protectedValue' => 'protectedValue@getProtectedValue()',
                    'publicValue' => 'publicValue@getPublicValue()',
                ],
            ],
            // accessing using UPPERCASED getters
            5 => [
                $this->createObjectWithUpperCaseGetter(),
                [
                    'privateValue' => 'privateValue@GETPRIVATEVALUE()',
                    'protectedValue' => 'protectedValue@GETPROTECTEDVALUE()',
                    'publicValue' => 'publicValue@GETPUBLICVALUE()',
                ],
            ],
            // accessing using magic method __call()
            6 => [
                $this->createObjectWithMagicCall(),
                [
                    'privateValue' => 'privateValue@__call(getPrivateValue)',
                ],
            ],
            7 => [
                $this->createObjectWithMagicCall(),
                [
                    'publicValue' => 'publicValue@__call(getPublicValue)',
                ],
            ],
            // accessing using magic method __get()
            8 => [
                $this->createObjectWithMagicGet(),
                [
                    'privateValue' => 'privateValue@__get(privateValue)',
                    'protectedValue' => 'protectedValue@__get(protectedValue)',
                    'publicValue' => 'publicValue',
                ],
            ],
            // accessing using everything
            9 => [
                $this->createObjectWithCamelCaseGetterAndMagicCall(),
                [
                    'privateValue' => 'privateValue@getPrivateValue()',
                    'protectedValue' => 'protectedValue@getProtectedValue()',
                    'publicValue' => 'publicValue@getPublicValue()',
                ],
            ],
            10 => [
                $this->createObjectWithCamelCaseGetterAndMagicGet(),
                [
                    'privateValue' => 'privateValue@getPrivateValue()',
                    'protectedValue' => 'protectedValue@getProtectedValue()',
                    'publicValue' => 'publicValue@getPublicValue()',
                ],
            ],
            11 => [
                $this->createObjectWithMagicCallAndMagicGet(),
                [
                    'privateValue' => 'privateValue@__call(getPrivateValue)',
                    'protectedValue' => 'protectedValue@__call(getProtectedValue)',
                    'publicValue' => 'publicValue@__call(getPublicValue)',
                ],
            ],
            12 => [
                $this->createObjectWithEverything(),
                [
                    'privateValue' => 'privateValue@getPrivateValue()',
                    'protectedValue' => 'protectedValue@getProtectedValue()',
                    'publicValue' => 'publicValue@getPublicValue()',
                ],
            ],
        ];
    }

    /**
     * @param object $object
     * @param array $properties
     * @param string|null $expectedErrorMessage
     *
     * @test
     * @dataProvider dataIsRenderedDataProvider
     */
    public function dataIsRendered($object, array $properties, $expectedErrorMessage = null)
    {
        $template = $this->createJsonFluidTemplate($properties, 'data.');
        $expectation = array_filter(
            array_values($properties),
            function ($propertyValue) {
                return $propertyValue !== null;
            }
        );

        $view = $this->getView();
        $view->getTemplatePaths()->setTemplateSource($template);
        $view->assign('data', $object);

        if ($expectedErrorMessage !== null && method_exists($this, 'expectErrorMessage')) {
            $this->expectErrorMessage($expectedErrorMessage);
            $view->render();
        } elseif ($expectedErrorMessage !== null) {
            try {
                $view->render();
            } catch (\Throwable $t) {
                static::assertSame($expectedErrorMessage, $t->getMessage());
            }
        } else {
            $result = json_decode($view->render(), true);
            static::assertSame($expectation, $result);
        }
    }

    /**
     * If your test case requires a custom View instance
     * return the instance from this method.
     *
     * @param bool $withCache
     * @return TemplateView
     */
    private function getView($withCache = false)
    {
        $view = new TemplateView();
        $cache = $this->getCache();
        if ($cache && $withCache) {
            $view->getRenderingContext()->setCache($cache);
        }
        return $view;
    }

    /**
     * If your test case requires a cache, override this
     * method and return an instance.
     *
     * @return FluidCacheInterface
     */
    private function getCache()
    {
        return new SimpleFileCache(sys_get_temp_dir());
    }

    /**
     * @param array $properties
     * @param string $variablePrefix
     * @return string
     */
    private function createJsonFluidTemplate(array $properties, $variablePrefix = '')
    {
        $inferences = array_map(
            function ($propertyName) use ($variablePrefix) {
                return sprintf('"{%s%s}"', $variablePrefix, $propertyName);
            },
            array_keys($properties)
        );
        return sprintf('[%s]', implode(', ', $inferences));
    }

    private function createObjectWithProperties()
    {
        return new Objects\WithProperties();
    }

    private function createObjectWithCamelCaseGetter()
    {
        return new Objects\WithCamelCaseGetter();
    }

    private function createObjectWithUpperCaseGetter()
    {
        return new Objects\WithUpperCaseGetter();
    }

    private function createObjectWithMagicCall()
    {
        return new Objects\WithMagicCall();
    }

    private function createObjectWithMagicGet()
    {
        return new Objects\WithMagicGet();
    }

    private function createObjectWithCamelCaseGetterAndMagicCall()
    {
        return new Objects\WithCamelCaseGetterAndMagicCall();
    }

    private function createObjectWithCamelCaseGetterAndMagicGet()
    {
        return new Objects\WithCamelCaseGetterAndMagicGet();
    }

    private function createObjectWithMagicCallAndMagicGet()
    {
        return new Objects\WithMagicCallAndMagicGet();
    }

    private function createObjectWithEverything()
    {
        return new Objects\WithEverything();
    }
}
