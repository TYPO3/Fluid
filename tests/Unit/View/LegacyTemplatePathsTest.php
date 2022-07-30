<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\View;

use TYPO3Fluid\Fluid\Tests\Unit\View\Fixtures\LegacyTemplatePathsFixture;

/**
 * Class TemplatePathsTest
 */
class LegacyTemplatePathsTest extends TemplatePathsTest
{
    /**
     * @return string
     */
    protected function getSubjectClassName()
    {
        return LegacyTemplatePathsFixture::class;
    }
}
