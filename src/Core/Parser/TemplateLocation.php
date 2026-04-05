<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

/**
 * Describes a location within a template file
 *
 * @api
 */
final readonly class TemplateLocation
{
    /**
     * @param string $identifierOrPath  internal name or path of a template file
     * @param int $line                 line number, starting with 1
     * @param int $character            character number within line, starting with 1
     */
    public function __construct(
        public string $identifierOrPath,
        public int $line,
        public int $character,
    ) {}
}
