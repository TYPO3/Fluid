<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core;

use TYPO3Fluid\Fluid\Core\Parser\TemplateLocation;

/**
 * Exception that is aware of the affected template file and
 * the exact line and character where the exception occurred.
 *
 * @api
 */
interface TemplateLocationException
{
    public function getTemplateLocation(): TemplateLocation;
}
