<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects;

use TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

class WithCamelCaseGetterAndMagicCall
{
    use Traits\PropertiesTrait;
    use Traits\CamelCaseGetterTrait;
    use Traits\MagicCallTrait;
}