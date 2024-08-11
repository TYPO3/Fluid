<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects;

class WithUpperCaseGetter
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

    public function GETPRIVATEVALUE(): string
    {
        return $this->privateValue . sprintf('@%s()', __FUNCTION__);
    }

    public function GETPROTECTEDVALUE(): string
    {
        return $this->protectedValue . sprintf('@%s()', __FUNCTION__);
    }

    public function GETPUBLICVALUE(): string
    {
        return $this->publicValue . sprintf('@%s()', __FUNCTION__);
    }
}
