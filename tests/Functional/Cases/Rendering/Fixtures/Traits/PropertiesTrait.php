<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Traits;

trait PropertiesTrait
{
    /**
     * @var string
     */
    private $privateValue = 'privateValue';

    /**
     * @var string
     */
    protected $protectedValue = 'protectedValue';

    /**
     * @var string
     */
    public $publicValue = 'publicValue';
}
