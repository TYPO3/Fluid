<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\Objects;

class WithEverything
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

    public function getPrivateValue(): string
    {
        return $this->privateValue . sprintf('@%s()', __FUNCTION__);
    }

    public function getProtectedValue(): string
    {
        return $this->protectedValue . sprintf('@%s()', __FUNCTION__);
    }

    public function getPublicValue(): string
    {
        return $this->publicValue . sprintf('@%s()', __FUNCTION__);
    }
}
