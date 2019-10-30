<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

trait UpperCaseGetterTrait
{
    use PropertiesTrait;

    /**
     * @return string
     */
    public function GETPRIVATEVALUE(): string
    {
        return $this->privateValue . sprintf('@%s()', __FUNCTION__);
    }

    /**
     * @return string
     */
    public function GETPROTECTEDVALUE(): string
    {
        return $this->protectedValue . sprintf('@%s()', __FUNCTION__);
    }

    /**
     * @return string
     */
    public function GETPUBLICVALUE(): string
    {
        return $this->publicValue . sprintf('@%s()', __FUNCTION__);
    }
}
