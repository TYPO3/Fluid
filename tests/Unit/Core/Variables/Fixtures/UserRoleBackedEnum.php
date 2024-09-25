<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables\Fixtures;

enum UserRoleBackedEnum: int
{
    case ADMIN = 1;
    case EDITOR = 2;
    case GUEST = 3;
}
