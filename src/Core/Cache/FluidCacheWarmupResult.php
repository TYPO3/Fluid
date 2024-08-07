<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Core\Cache;

use TYPO3Fluid\Fluid\Core\Compiler\FailedCompilingState;
use TYPO3Fluid\Fluid\Core\Parser\ParsedTemplateInterface;

/**
 * Class FluidCacheWarmupResult
 */
class FluidCacheWarmupResult
{
    public const RESULT_COMPILABLE = 'compilable';
    public const RESULT_COMPILED = 'compiled';
    public const RESULT_HASLAYOUT = 'hasLayout';
    public const RESULT_COMPILEDCLASS = 'compiledClassName';
    public const RESULT_FAILURE = 'failure';
    public const RESULT_MITIGATIONS = 'mitigations';

    protected array $results = [];

    public function merge(): static
    {
        /* @var FluidCacheWarmupResult[] $results */
        $results = func_get_args();
        foreach ($results as $result) {
            $this->results += $result->getResults();
        }
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function add(ParsedTemplateInterface $state, string $templatePathAndFilename): static
    {
        $currentlyCompiled = $state->isCompiled();
        $this->results[$templatePathAndFilename] = [
            static::RESULT_COMPILABLE => $currentlyCompiled || $state->isCompilable(),
            static::RESULT_COMPILED => $currentlyCompiled,
            static::RESULT_HASLAYOUT => $state->hasLayout(),
            static::RESULT_COMPILEDCLASS => $state->getIdentifier(),
        ];
        if ($state instanceof FailedCompilingState) {
            $this->results[$templatePathAndFilename][static::RESULT_FAILURE] = $state->getFailureReason();
            $this->results[$templatePathAndFilename][static::RESULT_MITIGATIONS] = $state->getMitigations();
        }
        return $this;
    }
}
