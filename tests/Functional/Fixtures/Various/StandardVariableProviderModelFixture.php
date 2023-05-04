<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

/**
 * Used by StandardVariableProviderTest
 */
class StandardVariableProviderModelFixture
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    public $existingPublicProperty = 'existingPublicPropertyValue';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNamed(): bool
    {
        return !empty($this->name);
    }

    public function hasSomeName(): bool
    {
        return !empty($this->name);
    }
}
