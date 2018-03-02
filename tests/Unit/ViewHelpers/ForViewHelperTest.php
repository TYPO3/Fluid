<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\CountableIterator;
use TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper;

/**
 * Testcase for ForViewHelper
 */
class ForViewHelperTest extends ViewHelperBaseTestcase
{

    public function setUp()
    {
        parent::setUp();


        $this->arguments['reverse'] = null;
        $this->arguments['key'] = null;
        $this->arguments['iteration'] = null;
    }

    /**
     * @test
     */
    public function renderExecutesTheLoopCorrectly()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);
        $this->arguments['each'] = [0, 1, 2, 3];
        $this->arguments['as'] = 'innerVariable';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            ['innerVariable' => 0],
            ['innerVariable' => 1],
            ['innerVariable' => 2],
            ['innerVariable' => 3]
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderPreservesKeys()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = ['key1' => 'value1', 'key2' => 'value2'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            [
                'innerVariable' => 'value1',
                'someKey' => 'key1'
            ],
            [
                'innerVariable' => 'value2',
                'someKey' => 'key2'
            ]
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfObjectIsNull()
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
    public function renderReturnsEmptyStringIfObjectIsEmptyArray()
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
    public function renderIteratesElementsInReverseOrderIfReverseIsTrue()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = [0, 1, 2, 3];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            ['innerVariable' => 3],
            ['innerVariable' => 2],
            ['innerVariable' => 1],
            ['innerVariable' => 0]
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderIteratesElementsInReverseOrderIfReverseIsTrueAndObjectIsIterator()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = new \ArrayIterator([0, 1, 2, 3]);
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            ['innerVariable' => 3],
            ['innerVariable' => 2],
            ['innerVariable' => 1],
            ['innerVariable' => 0]
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     * @dataProvider reverseDataProvider
     */
    public function renderPreservesKeysIfReverseIsTrue(array $each, array $expectedCallProtocol)
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = $each;
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';
        $this->arguments['reverse'] = true;

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @return array
     */
    public function reverseDataProvider()
    {
        return [
            'string keys' => [
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                [
                    [
                        'innerVariable' => 'value2',
                        'someKey' => 'key2',
                    ],
                    [
                        'innerVariable' => 'value1',
                        'someKey' => 'key1',
                    ],
                ],
            ],
            'numeric keys' => [
                [
                    'value1',
                    'value2',
                    'value3',
                ],
                [
                    [
                        'innerVariable' => 'value3',
                        'someKey' => 2,
                    ],
                    [
                        'innerVariable' => 'value2',
                        'someKey' => 1,
                    ],
                    [
                        'innerVariable' => 'value1',
                        'someKey' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function keyContainsNumericalIndexIfTheGivenArrayDoesNotHaveAKey()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = ['foo', 'bar', 'baz'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            [
                'innerVariable' => 'foo',
                'someKey' => 0
            ],
            [
                'innerVariable' => 'bar',
                'someKey' => 1
            ],
            [
                'innerVariable' => 'baz',
                'someKey' => 2
            ]
        ];
        $this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderThrowsExceptionWhenPassingObjectsToEachThatAreNotTraversable()
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
    public function renderIteratesThroughElementsOfTraversableObjects()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = new \ArrayObject(['key1' => 'value1', 'key2' => 'value2']);
        $this->arguments['as'] = 'innerVariable';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            ['innerVariable' => 'value1'],
            ['innerVariable' => 'value2']
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderPreservesKeyWhenIteratingThroughElementsOfObjectsThatImplementIteratorInterface()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = new \ArrayIterator(['key1' => 'value1', 'key2' => 'value2']);
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            [
                'innerVariable' => 'value1',
                'someKey' => 'key1'
            ],
            [
                'innerVariable' => 'value2',
                'someKey' => 'key2'
            ]
        ];
        $this->assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function keyContainsTheNumericalIndexWhenIteratingThroughElementsOfObjectsOfTyeSplObjectStorage()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $splObjectStorageObject = new \SplObjectStorage();
        $object1 = new \stdClass();
        $splObjectStorageObject->attach($object1);
        $object2 = new \stdClass();
        $splObjectStorageObject->attach($object2, 'foo');
        $object3 = new \stdClass();
        $splObjectStorageObject->offsetSet($object3, 'bar');

        $this->arguments['each'] = $splObjectStorageObject;
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['key'] = 'someKey';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            [
                'innerVariable' => $object1,
                'someKey' => 0
            ],
            [
                'innerVariable' => $object2,
                'someKey' => 1
            ],
            [
                'innerVariable' => $object3,
                'someKey' => 2
            ]
        ];
        $this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function iterationDataIsAddedToTemplateVariableContainerIfIterationArgumentIsSet()
    {
        $viewHelper = new ForViewHelper();

        $viewHelperNode = new ConstraintSyntaxTreeNode($this->templateVariableContainer);

        $this->arguments['each'] = ['foo' => 'bar', 'Flow' => 'Fluid', 'TYPO3' => 'rocks'];
        $this->arguments['as'] = 'innerVariable';
        $this->arguments['iteration'] = 'iteration';

        $this->injectDependenciesIntoViewHelper($viewHelper);
        $viewHelper->setViewHelperNode($viewHelperNode);
        $viewHelper->initializeArgumentsAndRender();

        $expectedCallProtocol = [
            [
                'innerVariable' => 'bar',
                'iteration' => [
                    'index' => 0,
                    'cycle' => 1,
                    'total' => 3,
                    'isFirst' => true,
                    'isLast' => false,
                    'isEven' => false,
                    'isOdd' => true
                ]
            ],
            [
                'innerVariable' => 'Fluid',
                'iteration' => [
                    'index' => 1,
                    'cycle' => 2,
                    'total' => 3,
                    'isFirst' => false,
                    'isLast' => false,
                    'isEven' => true,
                    'isOdd' => false
                ]
            ],
            [
                'innerVariable' => 'rocks',
                'iteration' => [
                    'index' => 2,
                    'cycle' => 3,
                    'total' => 3,
                    'isFirst' => false,
                    'isLast' => true,
                    'isEven' => false,
                    'isOdd' => true
                ]
            ]
        ];
        $this->assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }

    /**
     * @test
     */
    public function renderThrowsExceptionOnInvalidObject()
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
    public function renderCountsSubjectIfIterationArgumentProvided()
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
    public function renderDoesNotCountSubjectIfIterationArgumentNotProvided()
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
