<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Rendering\Fixtures\Objects;

class WithProperties
{
    /**
     * @var string
     */
    private $privateValue = 'privateValue'; // @phpstan-ignore-line - added on purpose, even if unused.

    /**
     * @var string
     */
    protected $protectedValue = 'protectedValue';

    /**
     * @var string
     */
    public $publicValue = 'publicValue';
}
