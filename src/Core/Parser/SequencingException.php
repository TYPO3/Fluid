<?php
declare(strict_types=1);

namespace TYPO3Fluid\Fluid\Core\Parser;

class SequencingException extends Exception
{
    protected $excerpt = '';

    protected $byte = 0;

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt): void
    {
        $this->excerpt = $excerpt;
    }

    public function getByte(): int
    {
        return $this->byte;
    }

    public function setByte(int $byte): void
    {
        $this->byte = $byte;
    }
}
