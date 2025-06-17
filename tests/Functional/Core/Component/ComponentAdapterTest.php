<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Component;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ComponentAdapterTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function throwsExceptionWithoutViewHelperNode(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionCode(1748773601);

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('invalid', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\InvalidComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<invalid:test />');
        $view->render();
    }

    #[Test]
    public function throwsExceptionWithoutViewHelperNodeCached(): void
    {
        self::expectException(Exception::class);
        self::expectExceptionCode(1748773601);

        try {
            $view = new TemplateView();
            $view->getRenderingContext()->setCache(self::$cache);
            $view->getRenderingContext()->getViewHelperResolver()->addNamespace('invalid', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\InvalidComponentCollection');
            $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<invalid:test />');
            $view->render();
        } catch (Exception) {
        }

        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('invalid', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\InvalidComponentCollection');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource('<invalid:test />');
        $view->render();
    }
}
