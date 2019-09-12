<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Parsing;

use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;

/**
 * Class NamespaceRegistrationTest
 */
class NamespaceRegistrationTest extends BaseFunctionalTestCase
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
            'Ignoring namespaces without conflict with registered namespace' => [
                '{namespace z*}{namespace bar}<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz /><f:format.raw>foobar</f:format.raw>',
                $this->variables,
                ['expected output' => '<zoo:bar /><bar:foo></bar:foo><zoo.bar:baz />'],
                ['not expected in output' => '<f:format.raw>foobar</f:format.raw>'],
            ],
            'Ignoring namespaces with conflict with registered namespace gives registered namespace priority' => [
                '{namespace f*}{namespace bar}<foo:bar /><bar:foo></bar:foo><foo.bar:baz /><f:format.raw>foobar</f:format.raw>',
                $this->variables,
                ['expected output' => '<foo:bar /><bar:foo></bar:foo><foo.bar:baz />'],
                ['not expected in output' => '<f:format.raw>foobar</f:format.raw>'],
            ],
        ];
    }
}
