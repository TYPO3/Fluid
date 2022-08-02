<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\Fixtures\ConstraintSyntaxTreeNode;
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
        self::assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
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

        self::assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
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
        self::assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
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
        self::assertEquals($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
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
        self::assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
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
        self::assertSame($expectedCallProtocol, $viewHelperNode->callProtocol, 'The call protocol differs -> The for loop does not work as it should!');
    }
}
