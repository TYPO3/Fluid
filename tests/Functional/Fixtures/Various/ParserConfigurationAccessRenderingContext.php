<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various;

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;

final class ParserConfigurationAccessRenderingContext extends RenderingContext
{
    public Configuration $parserConfiguration;

    public function buildParserConfiguration(): Configuration
    {
        $this->parserConfiguration = new Configuration();
        return $this->parserConfiguration;
    }
}
