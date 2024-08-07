<?php

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\FluidExamples;

use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\FluidExamples\ViewHelper\CustomViewHelper;

/**
 * Class MyCustomViewHelperResolver
 *
 * Custom ViewHelperResolver which is capable of
 * changing a wide array of details about how a
 * template gets parsed.
 */
class CustomViewHelperResolver extends ViewHelperResolver
{
    /**
     * Returns the built-in set of ViewHelper classes with
     * one addition, `f:myLink` which is redirected to anoter
     * class.
     */
    public function resolveViewHelperClassName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        if ($namespaceIdentifier === 'f' && $methodIdentifier === 'myLink') {
            return CustomViewHelper::class;
        }
        return parent::resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
    }

    /**
     * Asks the ViewHelper for argument definitions and adds
     * a case which matches our custom ViewHelper in order to
     * manipulate its argument definitions.
     *
     * @return ArgumentDefinition[]
     */
    public function getArgumentDefinitionsForViewHelper(ViewHelperInterface $viewHelper): array
    {
        $arguments = parent::getArgumentDefinitionsForViewHelper($viewHelper);
        if ($viewHelper instanceof CustomViewHelper) {
            $arguments['page'] = new ArgumentDefinition(
                'page',
                'array', // our argument must now be an array
                'This is our new description for the argument',
                false, // argument is no longer mandatory
                ['foo' => 'bar'], // our argument has a new default value if argument is not provided
            );
        }
        return $arguments;
    }
}
