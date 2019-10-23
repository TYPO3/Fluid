<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects;

use TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

class WithMagicCallAndMagicGet
{
    use Traits\PropertiesTrait;
    use Traits\MagicCallTrait;
    use Traits\MagicGetTrait;
}