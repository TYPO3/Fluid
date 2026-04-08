<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

use Throwable;
use TYPO3Fluid\Fluid\Core\TemplateLocationException;

/**
 * A Parsing Exception
 *
 * @api
 */
class Exception extends \TYPO3Fluid\Fluid\Core\Exception implements TemplateLocationException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        protected readonly ?TemplateLocation $templateLocation = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getTemplateLocation(): TemplateLocation
    {
        // @todo In the end, every parser exception should point to the location of the issue.
        //       Once we can guarantee that, we should make $templateLocation non-nullable and
        //       thus a required constructor argument (breaking change)
        return $this->templateLocation ?? new TemplateLocation('', 1, 1);
    }
}
