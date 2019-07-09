<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\Compiler;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;

/**
 * Class FailedCompilingState
 *
 * Replacement ParsingState used when a template fails to compile.
 * Includes additional reasons why compiling failed.
 */
class FailedCompilingState extends ParsingState implements ParsedTemplateInterface
{

    /**
     * @var string
     */
    protected $failureReason = '';

    /**
     * @var string[]
     */
    protected $mitigations = [];

    /**
     * @return string
     */
    public function getFailureReason(): string
    {
        return $this->failureReason;
    }

    /**
     * @param string $failureReason
     * @return void
     */
    public function setFailureReason(string $failureReason): void
    {
        $this->failureReason = $failureReason;
    }

    /**
     * @return array
     */
    public function getMitigations(): array
    {
        return $this->mitigations;
    }

    /**
     * @param array $mitigations
     */
    public function setMitigations(array $mitigations): void
    {
        $this->mitigations = $mitigations;
    }

    /**
     * @param string $mitigation
     * @return void
     */
    public function addMitigation(string $mitigation): void
    {
        $this->mitigations[] = $mitigation;
    }
}
