<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use Stringable;

/**
 * Interface for values that are considered safe HTML
 * and will not be escaped by the Fluid rendering engine.
 *
 * Use with caution and ensure the HTML has already been sanitized, as it will not be escaped by the Fluid rendering engine.
 *
 * @method string __toString() returns HTML that has already been sanitized and will not be escaped by the Fluid rendering engine.
 * @internal
 */
interface UnsafeHTML extends Stringable {}
