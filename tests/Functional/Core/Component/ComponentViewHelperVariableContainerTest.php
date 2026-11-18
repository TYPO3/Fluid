<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Component;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Verifies that ViewHelpers rendered inside a component can still access ViewHelperVariableContainer
 * state set up by an outer ViewHelper (e. g. a form ViewHelper), matching the behavior components need
 * to be usable as drop-in replacements for sitegeist/fluid-components.
 */
final class ComponentViewHelperVariableContainerTest extends AbstractFunctionalTestCase
{
    private function renderTemplate(string $source): string
    {
        $view = new TemplateView();
        $view->getRenderingContext()->setCache(self::$cache);
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('my', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\BasicComponentCollection');
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('formComponent', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ComponentCollections\FormComponentCollection');
        $view->getRenderingContext()->getViewHelperResolver()->addNamespace('form', 'TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MockForm');
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        return $view->render();
    }

    #[Test]
    public function outerViewHelperContextIsAvailableInsideComponent(): void
    {
        $source = '<form:context><formComponent:formFieldComponent name="field1" /></form:context>';
        $expected = '<input name="field1" />' . "\n" . '|fieldNames:field1';

        self::assertSame($expected, $this->renderTemplate($source), 'uncached');
        self::assertSame($expected, $this->renderTemplate($source), 'cached');
    }

    #[Test]
    public function fieldViewHelperWithoutOuterContextRendersWithoutIt(): void
    {
        $source = '<formComponent:formFieldComponent name="field1" />';
        $expected = '<input name="field1" without-context />' . "\n";

        self::assertSame($expected, $this->renderTemplate($source), 'uncached');
        self::assertSame($expected, $this->renderTemplate($source), 'cached');
    }

    #[Test]
    public function multipleComponentsInsideOuterContextAllRegisterThemselves(): void
    {
        $source = '<form:context><formComponent:formFieldComponent name="field1" /><formComponent:formFieldComponent name="field2" /></form:context>';
        $expected = '<input name="field1" />' . "\n" . '<input name="field2" />' . "\n" . '|fieldNames:field1,field2';

        self::assertSame($expected, $this->renderTemplate($source), 'uncached');
        self::assertSame($expected, $this->renderTemplate($source), 'cached');
    }

    #[Test]
    public function nestedComponentSlotsRemainIsolatedWhileSharingOuterContext(): void
    {
        $source = '<form:context><my:namedSlots><f:fragment name="test1"><formComponent:formFieldComponent name="field1" /></f:fragment></my:namedSlots></form:context>';
        $expected = '|<input name="field1" />' . "\n" . '|||' . "\n" . '|fieldNames:field1';

        self::assertSame($expected, $this->renderTemplate($source), 'uncached');
        self::assertSame($expected, $this->renderTemplate($source), 'cached');
    }
}
