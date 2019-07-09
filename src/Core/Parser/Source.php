<?php
declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Parser;

/**
 * Source reflecting a piece of Fluid source code,
 * usually coming from a template file.
 */
class Source
{
    public $source = '';
    public $bytes = [];
    public $length = 0;

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->bytes = unpack('C*', $source);
        $this->length = count($this->bytes);
    }
}
