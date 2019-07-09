<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Errors;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\Exception;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class ParsingErrorsTest
 */
class ParsingErrorsTest extends BaseFunctionalTestCase
{

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations(): array
    {
        return [
            'Unclosed ViewHelperNode' => [
                '<f:section name="Test"></div>',
                [],
                [],
                [],
                Exception::class
            ],
            'Missing required argument' => [
                '<f:section></f:section>',
                [],
                [],
                [],
                Exception::class
            ],
            'Uses invalid namespace' => [
                '<invalid:section></invalid:section>',
                [],
                [],
                [],
                Exception::class
            ],
        ];
    }
}
