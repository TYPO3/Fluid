<?php

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\ParsingState;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\BooleanNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase;
use TYPO3Fluid\Fluid\ViewHelpers\ElseViewHelper;
use TYPO3Fluid\Fluid\ViewHelpers\ThenViewHelper;

/**
 * Testcase for Condition ViewHelper
 */
class AbstractConditionViewHelperTest extends ViewHelperBaseTestcase
{

    /**
     * @var AbstractConditionViewHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewHelper;

    /**
     * @var ViewHelperNode|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewHelperNode;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, ['renderChildren', 'hasArgument']);
        $this->viewHelperNode = $this->getMockBuilder(ViewHelperNode::class)->disableOriginalConstructor()->getMock();
        $this->viewHelper->setViewHelperNode($this->viewHelperNode);
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
    }

    /**
     * @test
     * @dataProvider getCompileTestValues
     * @param array $childNodes
     * @param string $expected
     */
    public function testCompileReturnsAndAssignsExpectedVariables(array $childNodes, $expected)
    {
        $node = new ViewHelperNode($this->renderingContext, 'f', 'if', [], new ParsingState());
        foreach ($childNodes as $childNode) {
            $node->addChildNode($childNode);
        }
        $compiler = $this->getMock(
            TemplateCompiler::class,
            ['wrapChildNodesInClosure', 'wrapViewHelperNodeArgumentEvaluationInClosure']
        );
        $compiler->setRenderingContext($this->renderingContext);
        $compiler->expects(self::any())->method('wrapChildNodesInClosure')->willReturn('closure');
        $compiler->expects(self::any())->method('wrapViewHelperNodeArgumentEvaluationInClosure')->willReturn('arg-closure');
        $init = '';
        $this->viewHelper->compile('foobar-args', 'foobar-closure', $init, $node, $compiler);
        self::assertEquals($expected, $init);
    }

    /**
     * @return array
     */
    public function getCompileTestValues()
    {
        $state = new ParsingState();
        $context = new RenderingContextFixture();
        return [
            [
                [],
                'foobar-args[\'__thenClosure\'] = foobar-closure;' . chr(10)
            ],
            [
                [new ViewHelperNode($context, 'f', 'then', [], $state)],
                'foobar-args[\'__thenClosure\'] = closure;' . chr(10)
            ],
            [
                [new ViewHelperNode($context, 'f', 'else', [], $state)],
                'foobar-args[\'__elseClosures\'][] = closure;' . chr(10)
            ],
            [
                [
                    new ViewHelperNode($context, 'f', 'then', [], $state),
                    new ViewHelperNode($context, 'f', 'else', [], $state)
                ],
                'foobar-args[\'__thenClosure\'] = closure;' . chr(10) .
                'foobar-args[\'__elseClosures\'][] = closure;' . chr(10)
            ],
            [
                [
                    new ViewHelperNode($context, 'f', 'then', [], $state),
                    new ViewHelperNode($context, 'f', 'else', ['if' => new BooleanNode(new RootNode())], $state),
                    new ViewHelperNode($context, 'f', 'else', [], $state)
                ],
                'foobar-args[\'__thenClosure\'] = closure;' . chr(10) .
                'foobar-args[\'__elseClosures\'][] = closure;' . chr(10) .
                'foobar-args[\'__elseifClosures\'][] = arg-closure;' . chr(10) .
                'foobar-args[\'__elseClosures\'][] = closure;' . chr(10)
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getRenderFromArgumentsTestValues
     * @param array $arguments
     * @param $expected
     */
    public function testRenderFromArgumentsReturnsExpectedValue(array $arguments, $expected)
    {
        $viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, ['dummy']);
        $viewHelper->setArguments($arguments);
        $viewHelper->setViewHelperNode(new ViewHelperNode($this->renderingContext, 'f', 'if', [], new ParsingState()));
        $result = AbstractConditionViewHelper::renderStatic($arguments, function () {
            return '';
        }, $this->renderingContext);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderFromArgumentsTestValues()
    {
        return [
            [['condition' => false], null],
            [['condition' => true, '__thenClosure' => function () {
                return 'foobar';
            }], 'foobar'],
            [['condition' => true, '__elseClosures' => [function () {
                return 'foobar';
            }]], ''],
            [['condition' => true], ''],
            [['condition' => true], null],
            [['condition' => false, '__elseClosures' => [function () {
                return 'foobar';
            }]], 'foobar'],
            [['condition' => false, '__elseifClosures' => [
                function () {
                    return false;
                },
                function () {
                    return true;
                }
            ], '__elseClosures' => [
                function () {
                    return 'baz';
                },
                function () {
                    return 'foobar';
                }
            ]], 'foobar'],
            [['condition' => false, '__elseifClosures' => [
                function () {
                    return false;
                },
                function () {
                    return false;
                }
            ], '__elseClosures' => [
                function () {
                    return 'baz';
                },
                function () {
                    return 'foobar';
                },
                function () {
                    return 'barbar';
                }
            ]], 'barbar'],
            [['condition' => false, '__thenClosure' => function () {
                return 'foobar';
            }], ''],
            [['condition' => false], ''],
        ];
    }

    /**
     * @test
     */
    public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists()
    {
        $this->viewHelper->expects(self::any())->method('renderChildren')->willReturn('foo');
        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([]);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        self::assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists()
    {
        $mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], '', false);
        $mockThenViewHelperNode->expects(self::atLeastOnce())->method('getViewHelperClassName')->willReturn(ThenViewHelper::class);
        $mockThenViewHelperNode->expects(self::atLeastOnce())->method('evaluate')->with($this->renderingContext)->willReturn('ThenViewHelperResults');
        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([$mockThenViewHelperNode]);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        self::assertEquals('ThenViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified()
    {
        $this->viewHelper->expects(self::atLeastOnce())->method('hasArgument')->with('then')->willReturn(true);
        $this->arguments['then'] = 'ThenArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        self::assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper()
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], '', false);
        $mockElseViewHelperNode->expects(self::any())->method('getViewHelperClassName')->willReturn(ElseViewHelper::class);
        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([$mockElseViewHelperNode]);
        $this->viewHelper->expects(self::never())->method('renderChildren')->willReturn('Child nodes');

        $actualResult = $this->viewHelper->_call('renderThenChild');
        self::assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists()
    {
        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([]);
        $actualResult = $this->viewHelper->_call('renderElseChild');
        self::assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists()
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockElseViewHelperNode->expects(self::atLeastOnce())->method('getViewHelperClassName')->willReturn(ElseViewHelper::class);
        $mockElseViewHelperNode->expects(self::atLeastOnce())->method('evaluate')->with($this->renderingContext)->willReturn('ElseViewHelperResults');
        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([$mockElseViewHelperNode]);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        self::assertEquals('ElseViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndElseViewHelperChildIfArgumentConditionIsFalseToo()
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'getArguments', 'evaluate'], [], '', false);
        $mockElseViewHelperNode->expects(self::atLeastOnce())->method('getViewHelperClassName')->willReturn(ElseViewHelper::class);
        $mockElseViewHelperNode->expects(self::atLeastOnce())->method('getArguments')->willReturn(['if' => new BooleanNode(false)]);
        $mockElseViewHelperNode->expects(self::never())->method('evaluate');

        $this->viewHelperNode->expects(self::any())->method('getChildNodes')->willReturn([$mockElseViewHelperNode]);

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        self::assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue()
    {
        $mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockThenViewHelperNode->expects(self::never())->method('evaluate');

        $this->viewHelperNode->addChildNode($mockThenViewHelperNode);

        $this->viewHelper->expects(self::atLeastOnce())->method('hasArgument')->with('then')->willReturn(true);
        $this->arguments['then'] = 'ThenArgument';

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        self::assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsValueOfElseArgumentIfConditionIsFalse()
    {
        $this->viewHelper->expects(self::atLeastOnce())->method('hasArgument')->with('else')->willReturn(true);
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        self::assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @test
     */
    public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse()
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockElseViewHelperNode->expects(self::any())->method('getViewHelperClassName')->willReturn(ElseViewHelper::class);
        $mockElseViewHelperNode->expects(self::never())->method('evaluate');

        $this->viewHelperNode->addChildNode($mockElseViewHelperNode);

        $this->viewHelper->expects(self::atLeastOnce())->method('hasArgument')->with('else')->willReturn(true);
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        self::assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @param array $arguments
     * @param mixed $expected
     * @test
     * @dataProvider getRenderStaticTestValues
     */
    public function testRenderStatic(array $arguments, $expected)
    {
        $this->viewHelper->setArguments($arguments);
        $result = call_user_func_array(
            [$this->viewHelper, 'renderStatic'],
            [$arguments, function () {
                return '';
            }, new RenderingContextFixture()]
        );
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderStaticTestValues()
    {
        return [
            'standard then argument' => [['condition' => true, 'then' => 'yes'], 'yes'],
            'then argument closure' => [['condition' => true, '__thenClosure' => function () {
                return 'yes';
            }], 'yes'],
            'standard else argument' => [['condition' => false, 'else' => 'no'], 'no'],
            'single else argument closure' => [['condition' => false, '__elseClosures' => [function () {
                return 'no';
            }]], 'no'],
            'else if closures first match true' => [
                [
                    'condition' => false,
                    '__elseClosures' => [
                        function () {
                            return 'first-else';
                        },
                        function () {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function () {
                            return true;
                        },
                        function () {
                            throw new \RuntimeException('Test called closure which must not be called');
                        }
                    ]
                ],
                'first-else'
            ],
            'else if closures second match true' => [
                [
                    'condition' => false,
                    '__elseClosures' => [
                        function () {
                            return 'first-else';
                        },
                        function () {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function () {
                            return false;
                        },
                        function () {
                            return true;
                        }
                    ]
                ],
                'second-else'
            ],
            'else if closures none match' => [
                [
                    'condition' => false,
                    '__elseClosures' => [
                        function () {
                            return 'first-else';
                        },
                        function () {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function () {
                            return false;
                        },
                        function () {
                            return false;
                        }
                    ]
                ],
                ''
            ],
        ];
    }
}
