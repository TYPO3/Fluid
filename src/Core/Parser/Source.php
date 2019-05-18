<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\NewParser;

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
