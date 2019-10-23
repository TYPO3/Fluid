<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

trait MagicCallTrait
{
    use PropertiesTrait;

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed|null
     */
    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $propertyName = lcfirst(substr($name, 3));
        }
        if (isset($propertyName) && property_exists($this, $propertyName)) {
            return $this->{$propertyName} . sprintf('@__call(%s)', $name);
        }
        return null;
    }
}
