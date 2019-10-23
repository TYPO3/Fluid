<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

trait CamelCaseGetterTrait
{
    use PropertiesTrait;

    /**
     * @return string
     */
    public function getPrivateValue(): string
    {
        return $this->privateValue . sprintf('@%s()', __FUNCTION__);
    }

    /**
     * @return string
     */
    public function getProtectedValue(): string
    {
        return $this->protectedValue . sprintf('@%s()', __FUNCTION__);
    }

    /**
     * @return string
     */
    public function getPublicValue(): string
    {
        return $this->publicValue . sprintf('@%s()', __FUNCTION__);
    }
}
