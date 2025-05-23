<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\FluidExamples\Helper\ExampleHelper;

final class ExamplesTest extends AbstractFunctionalTestCase
{
    public static function exampleScriptValuesDataProvider(): array
    {
        return [
            'example_conditions.php' => [
                'example_conditions.php',
                [
                    'Standard ternary expression: The ternary expression is true',
                    'Negated ternary expression without then case: The ternary expression is false',
                    'Negated ternary expression: The ternary expression is false',
                    'Ternary expression without then case: The ternary expression is true',
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
                    '!($vartrue) === FALSE',
                    '$vararray1 == $vararray2 === FALSE',
                    '($vararray1 == $vararray1) && $vartrue === TRUE',
                    '$varfalse == $varfalse === TRUE',
                    '$varfalse != $varfalse === FALSE',
                    '$vararray1 == $vararray1 === TRUE',
                    '\'thisstring\' != \'thatstring\' === TRUE',
                ],
            ],
            'example_customresolving.php' => [
                'example_customresolving.php',
                [
                    var_export(['foo' => 'bar'], true),
                    var_export(['bar' => 'foo'], true),
                ],
            ],
            'example_format.php' => [
                'example_format.php',
                [
                    '"layout": "Default.json",',
                    '"foobar": "Variable foobar"',
                ],
            ],
            'example_layoutless.php' => [
                'example_layoutless.php',
                [
                    'This section is rendered below',
                    'This text is rendered because it is outside the section',
                ],
            ],
            'example_math.php' => [
                'example_math.php',
                [
                    'Expression: $numberten % 4 = 2',
                    'Expression: 4 * $numberten = 40',
                    'Expression: 4 / $numberten = ' . (0.4), // NOTE: concat'ing a float + string LC-casts the float to localized comma/t-sep. Hence, let PHP also cast the expected value.
                    'Expression: $numberone / $numberten = ' . (0.1), // NOTE: concat'ing a float + string LC-casts the float to localized comma/t-sep. Hence, let PHP also cast the expected value.
                    'Expression: 10 ^ $numberten = 10000000000',
                ],
            ],
            'example_multiplepaths.php' => [
                'example_multiplepaths.php',
                [
                    'Rendered via overridden Layout, section "Main":',
                    'Overridden Default template.',
                    'Value of "foobar": This is foobar.',
                    'Contents of FirstPartial.html',
                    'Overridden contents of SecondPartial.html',
                ],
            ],
            'example_mvc.php' => [
                'example_mvc.php',
                [
                    'I am the template belonging to the "Default" controller, action "Default".',
                    'I am the template belonging to the "Other" controller, action "Default".',
                    'I am the template belonging to the "Other" controller, action "List".',
                    'Value of "foobar": MVC template.',
                ],
            ],
            'example_namespaces.php' => [
                'example_namespaces.php',
                [
                    'Namespaces template',
                    '<invalid:vh>This tag will be shown</invalid:vh>',
                ],
            ],
            'example_namespaceresolving.php' => [
                'example_namespaceresolving.php',
                [
                    'NamespaceResolving template from Singles.',
                    'Argument passed to CustomViewHelper:',
                    '123',
                ],
            ],
            'example_single.php' => [
                'example_single.php',
                [
                    'Value of "foobar": Single template',
                ],
            ],
            'example_escapingmodifier.php' => [
                'example_escapingmodifier.php',
                [
                    'Value of "html": <strong>This is not escaped</strong>',
                    'From partial: <strong>This is not escaped</strong>',
                ],
            ],
            'example_structures.php' => [
                'example_structures.php',
                [
                    'Tag: "Dynamic"',
                    'Pass: "Dynamic"',
                    'Pipe: "Dynamic"',
                    'Pipe, multiple levels: "Dynamic"',
                    'This section exists and is rendered: Valid section',
                    'Expects no output because section name is invalid: ' . "\n",
                    'Dynamic section name: Dynamically suffixed section',
                    'Bad dynamic section name, expects fallback: Just a section',
                    'Will render: Just a section',
                    'Will render, clause reversed: Just a section',
                    'Will not render: ' . "\n",
                    'This `f:else` was rendered',
                    'The value was "3"',
                    'The unmatched value case triggered',
                    'The "b" nested switch case was triggered',
                ],
            ],
            'example_variables.php' => [
                'example_variables.php',
                [
                    'Simple variable: string foo',
                    'A string with numbers in it: 132',
                    'Ditto, with type name stored in variable: 132',
                    'A comma-separated value iterated as array:' . "\n\t- one\n\t- two",
                    'String variable name with dynamic1 part: String using $dynamic1.',
                    'String variable name with dynamic2 part: String using $dynamic2.',
                    'Array member in $array[$dynamic1]: Dynamic key in $array[$dynamic1]',
                    'Array member in $array[$dynamic2]: Dynamic key in $array[$dynamic2]',
                    'Output of variable whose name is stored in a variable: string foo',
                    'Direct access of numeric prefixed variable: Numeric prefixed variable',
                    'Aliased access of numeric prefixed variable: Numeric prefixed variable',
                    'Escaped ternary expression: &lt;b&gt;Unescaped string&lt;/b&gt;',
                    'Escaped cast expression: &lt;b&gt;Unescaped string&lt;/b&gt;',
                    'Received $array.foobar with value <b>Unescaped string</b> (same using "value" argument: <b>Unescaped string</b>)',
                    'Received $array.printf with formatted string Formatted string, value: formatted',
                    'Received $array.baz with value 42',
                    'Received $array.xyz.foobar with value Escaped sub-string',
                    'Received $myVariable with value Nice string',
                ],
            ],
            'example_variableprovider.php' => [
                'example_variableprovider.php',
                [
                    'VariableProvider template from Singles.',
                    'Random: random',
                ],
            ],
            'example_dynamiclayout.php' => [
                'example_dynamiclayout.php',
                [
                    'Rendered via DynamicLayout, section "Main":',
                ],
            ],
            'example_cachestatic.php' => [
                'example_cachestatic.php',
                [
                    'Cached as static text 1',
                    'Cached as static text 2',
                    'Cached as static text 3',
                ],
            ],
            'example_passthrough.php' => [
                'example_passthrough.php',
                [
                    '<f:format.raw>This does not get parsed; the source is passed through with Fluid markup</f:format.raw>',
                ],
            ],
            'example_errorhandling.php' => [
                'example_errorhandling.php',
                [
                    'View error: The Fluid template files',
                    'Section rendering error: Section "DoesNotExist" does not exist. Section rendering is mandatory; "optional" is false.',
                    'ViewHelper error: Undeclared arguments passed to ViewHelper TYPO3Fluid\Fluid\ViewHelpers\IfViewHelper: notregistered. Valid arguments are: then, else, condition - Offending code: <f:if notregistered="1" />',
                    'Parser error: The ViewHelper "<f:invalid>" could not be resolved.',
                    'Based on your spelling, the system would load the class "TYPO3Fluid\Fluid\ViewHelpers\InvalidViewHelper", however this class does not exist. Offending code: <f:invalid />',
                    'Invalid expression: Invalid target conversion type &quot;invalidtype&quot; specified in casting expression &quot;{foobar as invalidtype}&quot;.',
                ],
            ],
            'example_templatearguments.php' => [
                'example_templatearguments.php',
                [
                    'Title: My title',
                    'Tags: tag1, tag2',
                    'User: admin',
                ],
            ],
        ];
    }

    #[DataProvider('exampleScriptValuesDataProvider')]
    #[Test]
    public function exampleScriptValues(string $script, array $expectedOutputs): void
    {
        $scriptFile = __DIR__ . '/../../examples/' . $script;

        $outputCallback = function ($output) use ($expectedOutputs) {
            foreach ($expectedOutputs as $expectedOutput) {
                self::assertStringContainsString($expectedOutput, $output);
            }
        };

        ob_start();
        include $scriptFile;
        $output = ob_get_contents();
        ob_end_clean();
        call_user_func($outputCallback, $output);

        // Render a second time to verify caching works
        ob_start();
        include $scriptFile;
        $output = ob_get_contents();
        ob_end_clean();
        call_user_func($outputCallback, $output);
    }

    public static function tearDownAfterClass(): void
    {
        (new ExampleHelper())->cleanup();
        parent::tearDownAfterClass();
    }
}
