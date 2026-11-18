<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MockForm;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Minimal stand-in for a form field ViewHelper (such as TYPO3's TextfieldViewHelper) that reads
 * context from a surrounding ContextViewHelper via the ViewHelperVariableContainer and registers
 * its name for "trustedProperties". Used to test that this context remains available inside components.
 */
final class FieldViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Field name', true);
    }

    public function render(): string
    {
        $name = $this->arguments['name'];
        $variableContainer = $this->renderingContext->getViewHelperVariableContainer();

        if (!$variableContainer->exists(ContextViewHelper::class, 'fieldNames')) {
            return sprintf('<input name="%s" without-context />', $name);
        }

        $fieldNames = $variableContainer->get(ContextViewHelper::class, 'fieldNames');
        $fieldNames[] = $name;
        $variableContainer->addOrUpdate(ContextViewHelper::class, 'fieldNames', $fieldNames);

        return sprintf('<input name="%s" />', $name);
    }
}
