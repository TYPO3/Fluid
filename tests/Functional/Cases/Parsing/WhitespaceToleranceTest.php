<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class WhitespaceToleranceTest
 */
class WhitespaceToleranceTest extends BaseFunctionalTestCase
{

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        return [
            'Normal expected whitespace tolerance' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'No whitespace before self-close of tag' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"/>',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace before self-close of tag' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"      />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace before argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace after argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content    ="works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content= "works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace after argument name and before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content = "works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace before and after argument name' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content ="works" />',
                $this->variables,
                ['works'],
                [],
            ],
            'Extra whitespace before argument name and after argument name and before argument value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content = "works" />',
                $this->variables,
                ['works'],
                [],
            ],
        ];
    }
}
