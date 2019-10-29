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
            'plain array' => [
                ['value' => 'value'],
                ['value' => 'value'],
            ],
            'array object' => [
                new \ArrayObject(['value' => 'value']),
                ['value' => 'value'],
            ],
            'private properties fail' => [
                $this->createObjectWithProperties(),
                [
                    'privateValue' => null,
                ],
                'Cannot access private property TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects\WithProperties::$privateValue',
            ],
            'public property' => [
                $this->createObjectWithProperties(),
                [
                    'publicValue' => 'publicValue',
                ],
            ],
            'camelCase getter method' => [
                $this->createObjectWithCamelCaseGetter(),
                [
                    'privateValue' => 'privateValue@getPrivateValue()',
                    'protectedValue' => 'protectedValue@getProtectedValue()',
                    'publicValue' => 'publicValue@getPublicValue()',
                ],
            ],
            'UPPERCASE getter method' => [
                $this->createObjectWithUpperCaseGetter(),
                [
                    'privateValue' => 'privateValue@GETPRIVATEVALUE()',
                    'protectedValue' => 'protectedValue@GETPROTECTEDVALUE()',
                    'publicValue' => 'publicValue@GETPUBLICVALUE()',
                ],
            ],
            'multiple accessor types' => [
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

    private function createObjectWithEverything()
    {
        return new Objects\WithEverything();
    }
}
