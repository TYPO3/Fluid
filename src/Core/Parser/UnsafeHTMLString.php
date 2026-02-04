<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use Stringable;

/**
 * value object for values that are considered safe HTML
 * and will not be escaped by the Fluid rendering engine.
 *
 * Use with caution and ensure the HTML has already been sanitized, as it will not be escaped by the Fluid rendering engine.
 *
 * @internal
 */
final readonly class UnsafeHTMLString implements UnsafeHTML
{
    /**
     * @param string|Stringable $html HTML that has already been sanitized and will not be escaped by the Fluid rendering engine.
     */
    public function __construct(private string|Stringable $html) {}

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string)$this->html;
    }
}
