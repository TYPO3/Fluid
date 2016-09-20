<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class WhitespaceToleranceTest
 */
class WhitespaceToleranceTest extends BaseFunctionalTestCase {

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations() {
        return array(
            'Normal expected whitespace tolerance' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'No whitespace before self-close of tag' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"/>',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace before self-close of tag' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works"      />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace before argument name' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace after argument name' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content    ="works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace before argument value' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content= "works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace after argument name and before argument value' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content = "works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace before and after argument name' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content ="works" />',
                $this->variables,
                array('works'),
                array(),
            ),
            'Extra whitespace before argument name and after argument name and before argument value' => array(
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled  content = "works" />',
                $this->variables,
                array('works'),
                array(),
            ),
        );
    }

}
