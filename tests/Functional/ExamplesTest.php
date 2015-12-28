<?php
namespace FluidTYPO3Fluid\Flux\Tests\Unit\View;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use org\bovigo\vfs\vfsStream;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;

/**
 * Class ExamplesTest
 */
class ExamplesTest extends BaseTestCase {

	/**
	 * @return void
	 */
	public static function setUpBeforeClass() {
		vfsStream::setup('fakecache/');
	}

	/**
	 * @dataProvider getExampleScriptTestValues
	 * @param string $script
	 * @param array $expectedOutputs
	 * @param string $expectedException
	 */
	public function testExampleScriptFileWithoutCache($script, array $expectedOutputs, $expectedException = NULL) {
		if ($expectedException !== NULL) {
			$this->setExpectedException($expectedException);
		}
		$this->runExampleScriptTest($script, $expectedOutputs, FALSE);
	}

	/**
	 * @dataProvider getExampleScriptTestValues
	 * @param string $script
	 * @param array $expectedOutputs
	 * @param string $expectedException
	 */
	public function testExampleScriptFileWithCache($script, array $expectedOutputs, $expectedException = NULL) {
		if ($expectedException !== NULL) {
			$this->setExpectedException($expectedException);
		}
		$cache = vfsStream::url('fakecache/');
		$this->runExampleScriptTest($script, $expectedOutputs, $cache);
	}

	/**
	 * @param string $script
	 * @param array $expectedOutputs
	 * @param string $FLUID_CACHE_DIRECTORY
	 */
	protected function runExampleScriptTest($script, array $expectedOutputs, $FLUID_CACHE_DIRECTORY) {
		$scriptFile = __DIR__ . '/../../examples/' . $script;
		ob_start();
		include $scriptFile;
		$result = ob_get_clean();
		foreach ($expectedOutputs as $expectedOutput) {
			$this->assertContains($expectedOutput, $result);
		}
		unset($FLUID_CACHE_DIRECTORY);
	}

	/**
	 * @return array
	 */
	public function getExampleScriptTestValues() {
		return array(
			'example_conditions.php' => array(
				'example_conditions.php',
				array(
					'1 === TRUE',
					'(0) === FALSE',
					'(1) === TRUE',
					'0 && 0 === FALSE',
					'0 || 0 === FALSE',
					'1 && 0 === FALSE',
					'0 && 1 === FALSE',
					'1 || 0 === TRUE',
					'0 || 1 === TRUE',
					'0 || 1 && 0 === FALSE',
					'0 || 1 && 1 === TRUE',
					'$varfalse === FALSE',
					'$vartrue === TRUE',
					'$vararray1 == $vararray2 === FALSE',
					'($vararray1 == $vararray1) && $vartrue === TRUE',
					'$varfalse == $varfalse === TRUE',
					'$varfalse != $varfalse === FALSE',
					'$vararray1 == $vararray1 === TRUE',
					'\'thisstring\' != \'thatstring\' === TRUE'
				)
			),
			'example_customresolving.php' => array(
				'example_customresolving.php',
				array(
					var_export(array('foo' => 'bar'), TRUE),
					var_export(array('bar' => 'foo'), TRUE),
				)
			),
			'example_format.php' => array(
				'example_format.php',
				array(
					'"layout": "Default.json",',
					'"foobar": "Variable foobar"'
				)
			),
			'example_layoutless.php' => array(
				'example_layoutless.php',
				array(
					'This section is rendered below',
					'This text is rendered because it is outside the section'
				)
			),
			'example_math.php' => array(
				'example_math.php',
				array(
					'Expression: $numberten % 4 = 2',
					'Expression: 4 * $numberten = 40',
					'Expression: 4 / $numberten = 0.4',
					'Expression: $numberone / $numberten = 0.1',
					'Expression: 10 ^ $numberten = 10000000000'
				)
			),
			'example_multiplepaths.php' => array(
				'example_multiplepaths.php',
				array(
					'Rendered via overridden Layout, section "Main":',
					'Overridden Default template.',
					'Value of "foobar": This is foobar.',
					'Contents of FirstPartial.html',
					'Overridden contents of SecondPartial.html',
				)
			),
			'example_mvc.php' => array(
				'example_mvc.php',
				array(
					'I am the template belonging to the "Default" controller, action "Default".',
					'I am the template belonging to the "Other" controller, action "Default".',
					'I am the template belonging to the "Other" controller, action "List".',
					'Value of "foobar": MVC template.'
				)
			),
			'example_namespaces.php' => array(
				'example_namespaces.php',
				array(
					'Namespaces template',
					'<invalid:vh>This tag will be shown</invalid:vh>'
				),
				'\TYPO3Fluid\Fluid\Core\Parser\UnknownNamespaceException'
			),
			'example_namespaceresolving.php' => array(
				'example_namespaceresolving.php',
				array(
					'NamespaceResolving template from Singles.',
					'Argument passed to CustomViewHelper:',
					'123'
				)
			),
			'example_single.php' => array(
				'example_single.php',
				array(
					'Value of "foobar": Single template'
				)
			),
			'example_structures.php' => array(
				'example_structures.php',
				array(
					'This section exists and is rendered: Valid section',
					'Expects no output because section name is invalid: ' . PHP_EOL,
					'Dynamic section name: Dynamically suffixed section',
					'Bad dynamic section name, expects fallback: Just a section',
					'Will render: Just a section',
					'Will render, clause reversed: Just a section',
					'Will not render: ' . PHP_EOL,
					'This `f:else` was rendered',
					'The value was "3"',
					'The unmatched value case triggered',
					'The "b" nested switch case was triggered'
				)
			),
			'example_variables.php' => array(
				'example_variables.php',
				array(
					'A string with numbers in it: 132',
					'Ditto, with type name stored in variable: 132',
					'A comma-separated value iterated as array:' . PHP_EOL . "\t- one" . PHP_EOL . "\t- two",
					'String variable name with dynamic1 part: String using $dynamic1.',
					'String variable name with dynamic2 part: String using $dynamic2.',
					'Array member in $array[$dynamic1]: Dynamic key in $array[$dynamic1]',
					'Array member in $array[$dynamic2]: Dynamic key in $array[$dynamic2]',
					'Received $array.foobar with value Escaped string',
					'Received $array.baz with value 42',
					'Received $array.xyz.foobar with value Escaped sub-string',
					'Received $myVariable with value Nice string'
				)
			),
			'example_variableprovider.php' => array(
				'example_variableprovider.php',
				array(
					'VariableProvider template from Singles.',
					'Random: random',
				)
			)
		);
	}

}
