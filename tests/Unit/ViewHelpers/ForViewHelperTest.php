<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\CountableIterator;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\UserWithToString;
use TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper;

/**
 * Testcase for ForViewHelper
 */
class ForViewHelperTest extends ViewHelperBaseTestcase
{

    public function setUp(): void
    {
        parent::setUp();


        $this->arguments['reverse'] = null;
        $this->arguments['key'] = null;
        $this->arguments['iteration'] = null;
    }

    /**
     * @test
     */
    public function renderExecutesTheLoopCorrectly(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = [0, 1, 2, 3];
        $this->arguments['as'] = 'innerVariable';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($output, '0123', 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderPreservesKeys(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('someKey'));
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = ['key1' => 'value1', 'key2' => 'value2'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($output, 'key1value1key2value2', 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfObjectIsNull(): void
    {
        $viewHelper = new ForViewHelper();

        $this->arguments['each'] = null;
        $this->arguments['as'] = 'foo';

        $this->injectDependenciesIntoViewHelper($viewHelper);

        $this->assertEquals('', $viewHelper->initializeArgumentsAndRender());
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfObjectIsEmptyArray(): void
    {
        $viewHelper = new ForViewHelper();

        $this->arguments['each'] = [];
        $this->arguments['as'] = 'foo';

        $this->injectDependenciesIntoViewHelper($viewHelper);

        $this->assertEquals('', $viewHelper->initializeArgumentsAndRender());
    }

    /**
     * @test
     */
    public function renderIteratesElementsInReverseOrderIfReverseIsTrue(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = [0, 1, 2, 3];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($output, '3210', 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderIteratesElementsInReverseOrderIfReverseIsTrueAndObjectIsIterator(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = new \ArrayIterator([0, 1, 2, 3]);
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($output, '3210', 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     * @dataProvider reverseDataProvider
     */
    public function renderPreservesKeysIfReverseIsTrue(array $each, string $expected): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('someKey'));
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = $each;
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($expected, $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @return array
     */
    public function reverseDataProvider(): array
    {
        return [
            'string keys' => [
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                'key2value2key1value1',
            ],
            'numeric keys' => [
                [
                    'value1',
                    'value2',
                    'value3',
                ],
                '2value31value20value1',
            ],
        ];
    }

    /**
     * @test
     */
    public function keyContainsNumericalIndexIfTheGivenArrayDoesNotHaveAKey(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('someKey'));
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = ['foo', 'bar', 'baz'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertSame('0foo1bar2baz', $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderThrowsExceptionWhenPassingObjectsToEachThatAreNotTraversable(): void
    {
        $viewHelper = new ForViewHelper();
        $object = new \stdClass();

        $this->arguments['each'] = $object;
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';
        $this->arguments['reverse'] = true;

        $this->setExpectedException(\InvalidArgumentException::class);
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->initializeArgumentsAndRender();
    }


    /**
     * @test
     */
    public function renderIteratesThroughElementsOfTraversableObjects(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = new \ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $this->arguments['as'] = 'innerVariable';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals('value1value2', $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderPreservesKeyWhenIteratingThroughElementsOfObjectsThatImplementIteratorInterface(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('someKey'));
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = new \ArrayIterator(['key1' => 'value1', 'key2' => 'value2']);
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals('key1value1key2value2', $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function keyContainsTheNumericalIndexWhenIteratingThroughElementsOfObjectsOfTyeSplObjectStorage(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('someKey'));
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $splObjectStorageObject = new \SplObjectStorage();
        $object1 = new UserWithToString('foo');
        $splObjectStorageObject->attach($object1);
        $object2 = new UserWithToString('bar');
        $splObjectStorageObject->attach($object2, 'foo');
        $object3 = new UserWithToString('baz');
        $splObjectStorageObject->offsetSet($object3, 'bar');

        $this->arguments['each'] = $splObjectStorageObject;
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertSame('0foo1bar2baz', $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function iterationDataIsAddedToTemplateVariableContainerIfIterationArgumentIsSet(): void
    {
        $viewHelper = new ForViewHelper();
        $viewHelper->addChildNode(new ObjectAccessorNode('innerVariable'));

        $this->arguments['each'] = ['foo' => 'bar', 'Flow' => 'Fluid', 'TYPO3' => 'rocks'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['iteration'] = 'iteration';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $output = $viewHelper->initializeArgumentsAndRender();

        $this->assertSame('barFluidrocks', $output, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderThrowsExceptionOnInvalidObject(): void
    {
        $viewHelper = new ForViewHelper();
        $this->arguments['each'] = new \DateTime('now');
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $this->setExpectedException(Exception::class);
        $viewHelper->render();
    }

    /**
     * @test
     */
    public function renderCountsSubjectIfIterationArgumentProvided(): void
    {
        $subject = $this->getMockBuilder(CountableIterator::class)->setMethods(['count'])->getMock();
        $subject->expects($this->once())->method('count')->willReturn(1);
        $viewHelper = new ForViewHelper();
        $this->arguments['each'] = $subject;
        $this->arguments['iteration'] = 'test';
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->render();
    }
    /**
     * @test
     */
    public function renderDoesNotCountSubjectIfIterationArgumentNotProvided(): void
    {
        $subject = $this->getMockBuilder(CountableIterator::class)->setMethods(['count'])->getMock();
        $subject->expects($this->never())->method('count');
        $viewHelper = new ForViewHelper();
        $this->arguments['each'] = $subject;
        $this->arguments['iteration'] = null;
        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->render();
    }
}
