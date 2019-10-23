<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

trait MagicGetTrait
{
    use PropertiesTrait;

    /**
     * @param string $propertyName
     * @return mixed|null
     */
    public function __get(string $propertyName)
    {
        if (property_exists($this, $propertyName)) {
            return $this->{$propertyName} . sprintf('@__get(%s)', $propertyName);
        }
        return null;
    }
}
