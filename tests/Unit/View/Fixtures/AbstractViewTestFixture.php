<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View\Fixtures;

use TYPO3Fluid\Fluid\View\AbstractView;

/**
 * Fixture to test AbstractView
 */
class AbstractViewTestFixture extends AbstractView
{
    /**
     * Made public
     */
    public $variables = [];

    /**
     * Implement interface
     */
    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false)
    {
        return '';
    }

    /**
     * Implement interface
     */
    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false)
    {
        return '';
    }
}
