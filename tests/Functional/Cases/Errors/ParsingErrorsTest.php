<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Errors;

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
    public function getTemplateCodeFixturesAndExpectations()
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
