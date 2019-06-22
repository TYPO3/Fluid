<?php
namespace TYPO3Fluid\Fluid\Core\Parser;

use TYPO3Fluid\Fluid\Core\Exception;
/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */
/**
 * Exception which when thrown causes the template rendering
 * to output the full source of the Fluid template file rather
 * than allow it to be parsed.
 *
 * @api
 */
class PassthroughSourceException extends Exception
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }
}
