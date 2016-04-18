<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class EscapingTest
 */
class EscapingTest extends BaseFunctionalTestCase {

	/**
	 * @var array
	 */
	protected $variables = array('settings' => array('test' => '<strong>Bla</strong>'));

	/**
	 * @return array
	 */
	public function getTemplateCodeFixturesAndExpectations() {
		return array(
			'escapeChildren can be disabled in template' => array(
				'{escapingEnabled=false}<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'escapeOutput can be disabled in template' => array(
				'{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'Disabling escaping twice in template throws parsing exception' => array(
				'{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>{escapingEnabled=false}',
				array(),
				array(),
				array(),
				'TYPO3Fluid\\Fluid\\Core\\Parser\\Exception'
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => array(
				'<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
				$this->variables,
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
				array('<strong>Bla</strong>'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => array(
				'{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}',
				$this->variables,
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
				array('<strong>Bla</strong>'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => array(
				'<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => array(
				'{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => array(
				'{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does encode variable value (encoded before passed to VH)' => array(
				'{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
				$this->variables,
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
				array('<strong>Bla</strong>'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => array(
				'<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => array(
				'<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => array(
				'<test:escapeChildrenDisabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputDisabled>',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => array(
				'{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => array(
				'<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => array(
				'{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => array(
				'{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does encode variable value (encoded before passed to VH)' => array(
				'{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
				$this->variables,
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
				array('<strong>Bla</strong>'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => array(
				'<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
			'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => array(
				'<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}" />',
				$this->variables,
				array('<strong>Bla</strong>'),
				array('&lt;strong&gt;Bla&lt;/strong&gt;'),
			),
		);
	}

}
