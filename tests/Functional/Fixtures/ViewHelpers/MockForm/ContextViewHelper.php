<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MockForm;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Minimal stand-in for a form ViewHelper (such as TYPO3's FormViewHelper) that stores state in the
 * ViewHelperVariableContainer for nested field ViewHelpers to consume, e. g. to collect field names
 * for "trustedProperties". Used to test that this context remains available inside components.
 */
final class ContextViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function render(): string
    {
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();
        $variableContainer->addOrUpdate(self::class, 'fieldNames', []);

        $content = $this->renderChildren();

        $fieldNames = $variableContainer->get(self::class, 'fieldNames', []);
        $variableContainer->remove(self::class, 'fieldNames');

        return $content . '|fieldNames:' . implode(',', $fieldNames);
    }
}
