<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Parser;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

/**
 * Sequencing exception thrown by Sequencer
 */
class SequencingException extends Exception
{
    protected $excerpt = '';

    protected $byte = 0;

    protected $file = '';

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

    public function setLine($line): void
    {
        $this->line = $line;
    }

    public function setFile(string $file): void
    {
        $this->file = $file;
    }
}
