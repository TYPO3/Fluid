<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\View\Exception;

use Throwable;
use TYPO3Fluid\Fluid\View;

/**
 * An "Invalid Template Resource" exception
 *
 * @api
 */
class InvalidTemplateResourceException extends View\Exception
{
    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous = null,
        public readonly string $templateName = '',
        /** @var string[] */
        public readonly array $evaluatedTemplatePaths = [],
    ) {
        parent::__construct($message, $code, $previous);
    }
}
