<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Testcase for AbstractViewHelper
 *
 */
class AbstractViewHelperTest extends UnitTestCase
{

    /**
     * @var array
     */
    protected $fixtureMethodParameters = [
        'param1' => [
            'position' => 0,
            'optional' => false,
            'type' => 'integer',
            'defaultValue' => null
        ],
        'param2' => [
            'position' => 1,
            'optional' => false,
            'type' => 'array',
            'array' => true,
            'defaultValue' => null
        ],
        'param3' => [
            'position' => 2,
            'optional' => true,
            'type' => 'string',
            'array' => false,
            'defaultValue' => 'default'
        ],
    ];

    /**
     * @var array
     */
    protected $fixtureMethodTags = [
        'param' => [
            'integer $param1 P1 Stuff',
            'array $param2 P2 Stuff',
            'string $param3 P3 Stuff'
        ]
    ];

    /**
     * @param mixed $input
     * @param mixed $expected
     * @dataProvider getFirstElementOfNonEmptyTestValues
     */
    public function testGetFirstElementOfNonEmpty($input, $expected)
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['dummy']);
        $this->assertEquals($expected, $viewHelper->_call('getFirstElementOfNonEmpty', $input));
    }

    /**
     * @return array
     */
    public function getFirstElementOfNonEmptyTestValues()
    {
        return [
            'plain array' => [['foo', 'bar'], 'foo'],
            'iterator w/o arrayaccess' => [new \IteratorIterator(new \ArrayIterator(['foo', 'bar'])), 'foo'],
            'unsupported value' => ['unsupported value', null]
        ];
    }

    /**
     * @test
     */
    public function argumentsCanBeRegistered()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, null, [], '', false);

        $name = 'This is a name';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;
        $expected = new ArgumentDefinition($name, $type, $description, $isRequired);

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $this->assertEquals([$name => $expected], $viewHelper->prepareArguments(), 'Argument definitions not returned correctly.');
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function registeringTheSameArgumentNameAgainThrowsException()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, null, [], '', false);

        $name = 'shortName';
        $description = 'Example desc';
        $type = 'string';
        $isRequired = true;

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $viewHelper->_call('registerArgument', $name, 'integer', $description, $isRequired);
    }

    /**
     * @test
     */
    public function overrideArgumentOverwritesExistingArgumentDefinition()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, null, [], '', false);

        $name = 'argumentName';
        $description = 'argument description';
        $overriddenDescription = 'overwritten argument description';
        $type = 'string';
        $overriddenType = 'integer';
        $isRequired = true;
        $expected = new ArgumentDefinition($name, $overriddenType, $overriddenDescription, $isRequired);

        $viewHelper->_call('registerArgument', $name, $type, $description, $isRequired);
        $viewHelper->_call('overrideArgument', $name, $overriddenType, $overriddenDescription, $isRequired);
        $this->assertEquals($viewHelper->prepareArguments(), [$name => $expected], 'Argument definitions not returned correctly. The original ArgumentDefinition could not be overridden.');
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function overrideArgumentThrowsExceptionWhenTryingToOverwriteAnNonexistingArgument()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, null, [], '', false);

        $viewHelper->_call('overrideArgument', 'argumentName', 'string', 'description', true);
    }

    /**
     * @test
     */
    public function prepareArgumentsCallsInitializeArguments()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['initializeArguments'], [], '', false);

        $viewHelper->expects($this->once())->method('initializeArguments');

        $viewHelper->prepareArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsCallsPrepareArguments()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue([]));

        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsAcceptsAllObjectsImplemtingArrayAccessAsAnArray()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->setArguments(['test' => new \ArrayObject]);
        $viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue(['test' => new ArgumentDefinition('test', 'array', false, 'documentation')]));
        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function validateArgumentsCallsTheRightValidators()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->setArguments(['test' => 'Value of argument']);

        $viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue([
            'test' => new ArgumentDefinition('test', 'string', false, 'documentation')
        ]));

        $viewHelper->validateArguments();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function validateArgumentsCallsTheRightValidatorsAndThrowsExceptionIfValidationIsWrong()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->setArguments(['test' => 'test']);

        $viewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue([
            'test' => new ArgumentDefinition('test', 'stdClass', false, 'documentation')
        ]));

        $viewHelper->validateArguments();
    }

    /**
     * @test
     */
    public function initializeArgumentsAndRenderCallsTheCorrectSequenceOfMethods()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['validateArguments', 'initialize', 'callRenderMethod']);
        $viewHelper->expects($this->at(0))->method('validateArguments');
        $viewHelper->expects($this->at(1))->method('initialize');
        $viewHelper->expects($this->at(2))->method('callRenderMethod')->will($this->returnValue('Output'));

        $expectedOutput = 'Output';
        $actualOutput = $viewHelper->initializeArgumentsAndRender(['argument1' => 'value1']);
        $this->assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * @test
     */
    public function setRenderingContextShouldSetInnerVariables()
    {
        $templateVariableContainer = $this->getMock(StandardVariableProvider::class);
        $viewHelperVariableContainer = $this->getMock(ViewHelperVariableContainer::class);

        $view = new TemplateView();
        $renderingContext = new RenderingContext($view);
        $renderingContext->setVariableProvider($templateVariableContainer);
        $renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);

        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['prepareArguments'], [], '', false);

        $viewHelper->setRenderingContext($renderingContext);

        $this->assertSame($viewHelper->_get('templateVariableContainer'), $templateVariableContainer);
        $this->assertSame($viewHelper->_get('viewHelperVariableContainer'), $viewHelperVariableContainer);
    }

    /**
     * @test
     */
    public function testRenderChildrenCallsRenderChildrenClosureIfSet()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, null, [], '', false);
        $viewHelper->setRenderChildrenClosure(function () {
            return 'foobar';
        });
        $result = $viewHelper->renderChildren();
        $this->assertEquals('foobar', $result);
    }

    /**
     * @test
     * @dataProvider getValidateArgumentsTestValues
     * @param ArgumentDefinition $argument
     * @param mixed $value
     */
    public function testValidateArguments(ArgumentDefinition $argument, $value)
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractViewHelper::class,
            ['hasArgument', 'prepareArguments'],
            [],
            '',
            false
        );
        $viewHelper->expects($this->once())->method('prepareArguments')->willReturn(
            [$argument->getName() => $argument, 'second' => $argument]
        );
        $viewHelper->setArguments([$argument->getName() => $value, 'second' => $value]);
        $viewHelper->expects($this->at(1))->method('hasArgument')->with($argument->getName())->willReturn(true);
        $viewHelper->expects($this->at(2))->method('hasArgument')->with('second')->willReturn(true);
        $viewHelper->validateArguments();
    }

    /**
     * @return array
     */
    public function getValidateArgumentsTestValues()
    {
        return [
            [new ArgumentDefinition('test', 'boolean', '', true, false), false],
            [new ArgumentDefinition('test', 'boolean', '', true), true],
            [new ArgumentDefinition('test', 'string', '', true), 'foobar'],
            [new ArgumentDefinition('test', 'string', '', true), new UserWithToString('foobar')],
            [new ArgumentDefinition('test', 'array', '', true), ['foobar']],
            [new ArgumentDefinition('test', 'mixed', '', true), new \DateTime('now')],
            [new ArgumentDefinition('test', 'DateTime[]', '', true), [new \DateTime('now'), 'test']],
            [new ArgumentDefinition('test', 'string[]', '', true), []],
            [new ArgumentDefinition('test', 'string[]', '', true), ['foobar']],
        ];
    }

    /**
     * @test
     * @dataProvider getValidateArgumentsErrorsTestValues
     * @param ArgumentDefinition $argument
     * @param mixed $value
     */
    public function testValidateArgumentsErrors(ArgumentDefinition $argument, $value)
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractViewHelper::class,
            ['hasArgument', 'prepareArguments'],
            [],
            '',
            false
        );
        $viewHelper->expects($this->once())->method('prepareArguments')->willReturn([$argument->getName() => $argument]);
        $viewHelper->expects($this->once())->method('hasArgument')->with($argument->getName())->willReturn(true);
        $viewHelper->setArguments([$argument->getName() => $value]);
        $this->setExpectedException('InvalidArgumentException');
        $viewHelper->validateArguments();
    }

    /**
     * @return array
     */
    public function getValidateArgumentsErrorsTestValues()
    {
        return [
            [new ArgumentDefinition('test', 'boolean', '', true), ['bad']],
            [new ArgumentDefinition('test', 'string', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'DateTime', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'DateTime', '', true), 'test'],
            [new ArgumentDefinition('test', 'integer', '', true), new \ArrayIterator(['bar'])],
            [new ArgumentDefinition('test', 'object', '', true), 'test'],
            [new ArgumentDefinition('test', 'string[]', '', true), [new \DateTime('now'),'test']]
        ];
    }

    /**
     * @test
     */
    public function testValidateAdditionalArgumentsThrowsExceptionIfNotEmpty()
    {
        $viewHelper = $this->getAccessibleMock(
            AbstractViewHelper::class,
            ['dummy'],
            [],
            '',
            false
        );
        $this->setExpectedException(Exception::class);
        $viewHelper->validateAdditionalArguments(['foo' => 'bar']);
    }

    /**
     * @test
     */
    public function testCompileReturnsAndAssignsExpectedPhpCode()
    {
        $view = new TemplateView();
        $context = new RenderingContext($view);
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['dummy'], [], '', false);
        $node = new ViewHelperNode($context, 'f', 'comment', [], new ParsingState());
        $init = '';
        $compiler = new TemplateCompiler();
        $result = $viewHelper->compile('foobar', 'baz', $init, $node, $compiler);
        $this->assertEmpty($init);
        $this->assertEquals(get_class($viewHelper) . '::renderStatic(foobar, baz, $renderingContext)', $result);
    }

    /**
     * @test
     */
    public function testDefaultResetStateMethodDoesNothing()
    {
        $viewHelper = $this->getAccessibleMock(AbstractViewHelper::class, ['dummy'], [], '', false);
        $this->assertNull($viewHelper->resetState());
    }
}
