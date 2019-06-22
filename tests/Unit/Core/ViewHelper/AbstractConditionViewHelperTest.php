<?php
namespace TYPO3Fluid\Fluid\Tests\Unit\Core\ViewHelper;

use PHPUnit\Framework\MockObject\MockObject;
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
     * @var AbstractConditionViewHelper|MockObject
     */
    protected $viewHelper;

    /**
     * @var ViewHelperNode|MockObject
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
    public function testCompileReturnsAndAssignsExpectedVariables(array $childNodes, string $expected): void
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
        $compiler->expects($this->any())->method('wrapChildNodesInClosure')->willReturn('closure');
        $compiler->expects($this->any())->method('wrapViewHelperNodeArgumentEvaluationInClosure')->willReturn('arg-closure');
        $init = '';
        $this->viewHelper->compile('foobar-args', 'foobar-closure', $init, $node, $compiler);
        $this->assertEquals($expected, $init);
    }

    /**
     * @return array
     */
    public function getCompileTestValues(): array
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
    public function testRenderFromArgumentsReturnsExpectedValue(array $arguments, $expected): void
    {
        $viewHelper = $this->getAccessibleMock(AbstractConditionViewHelper::class, ['dummy']);
        $viewHelper->setArguments($arguments);
        $viewHelper->setViewHelperNode(new ViewHelperNode($this->renderingContext, 'f', 'if', [], new ParsingState()));
        $result = AbstractConditionViewHelper::renderStatic($arguments, function (): string {
            return '';
        }, $this->renderingContext);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderFromArgumentsTestValues(): array
    {
        return [
            [['condition' => false], null],
            [['condition' => true, '__thenClosure' => function (): string {
                return 'foobar';
            }], 'foobar'],
            [['condition' => true, '__elseClosures' => [function (): string {
                return 'foobar';
            }]], ''],
            [['condition' => true], ''],
            [['condition' => true], null],
            [['condition' => false, '__elseClosures' => [function (): string {
                return 'foobar';
            }]], 'foobar'],
            [['condition' => false, '__elseifClosures' => [
                function (): bool {
                    return false;
                },
                function (): bool {
                    return true;
                }
            ], '__elseClosures' => [
                function (): string {
                    return 'baz';
                },
                function (): string {
                    return 'foobar';
                }
            ]], 'foobar'],
            [['condition' => false, '__elseifClosures' => [
                function (): bool {
                    return false;
                },
                function (): bool {
                    return false;
                }
            ], '__elseClosures' => [
                function (): string {
                    return 'baz';
                },
                function (): string {
                    return 'foobar';
                },
                function (): string {
                    return 'barbar';
                }
            ]], 'barbar'],
            [['condition' => false, '__thenClosure' => function (): string {
                return 'foobar';
            }], ''],
            [['condition' => false], ''],
        ];
    }

    /**
     * @test
     */
    public function renderThenChildReturnsAllChildrenIfNoThenViewHelperChildExists(): void
    {
        $this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('foo'));
        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([]));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('foo', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsThenViewHelperChildIfConditionIsTrueAndThenViewHelperChildExists(): void
    {
        $mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], '', false);
        $mockThenViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue(ThenViewHelper::class));
        $mockThenViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ThenViewHelperResults'));
        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockThenViewHelperNode]));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsValueOfThenArgumentIfItIsSpecified(): void
    {
        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(true));
        $this->arguments['then'] = 'ThenArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderThenChildReturnsEmptyStringIfChildNodesOnlyContainElseViewHelper(): void
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate'], [], '', false);
        $mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));
        $this->viewHelper->expects($this->never())->method('renderChildren')->will($this->returnValue('Child nodes'));

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndNoElseViewHelperChildExists(): void
    {
        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([]));
        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildRendersElseViewHelperChildIfConditionIsFalseAndNoThenViewHelperChildExists(): void
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
        $mockElseViewHelperNode->expects($this->at(1))->method('evaluate')->with($this->renderingContext)->will($this->returnValue('ElseViewHelperResults'));
        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseViewHelperResults', $actualResult);
    }

    /**
     * @test
     */
    public function renderElseChildReturnsEmptyStringIfConditionIsFalseAndElseViewHelperChildIfArgumentConditionIsFalseToo(): void
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'getArguments', 'evaluate'], [], '', false);
        $mockElseViewHelperNode->expects($this->at(0))->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
        $mockElseViewHelperNode->expects($this->at(1))->method('getArguments')->will($this->returnValue(['if' => new BooleanNode(false)]));
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelperNode->expects($this->any())->method('getChildNodes')->will($this->returnValue([$mockElseViewHelperNode]));

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('', $actualResult);
    }

    /**
     * @test
     */
    public function thenArgumentHasPriorityOverChildNodesIfConditionIsTrue(): void
    {
        $mockThenViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockThenViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelperNode->addChildNode($mockThenViewHelperNode);

        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('then')->will($this->returnValue(true));
        $this->arguments['then'] = 'ThenArgument';

        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderThenChild');
        $this->assertEquals('ThenArgument', $actualResult);
    }

    /**
     * @test
     */
    public function renderReturnsValueOfElseArgumentIfConditionIsFalse(): void
    {
        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(true));
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @test
     */
    public function elseArgumentHasPriorityOverChildNodesIfConditionIsFalse(): void
    {
        $mockElseViewHelperNode = $this->getMock(ViewHelperNode::class, ['getViewHelperClassName', 'evaluate', 'setRenderingContext'], [], '', false);
        $mockElseViewHelperNode->expects($this->any())->method('getViewHelperClassName')->will($this->returnValue(ElseViewHelper::class));
        $mockElseViewHelperNode->expects($this->never())->method('evaluate');

        $this->viewHelperNode->addChildNode($mockElseViewHelperNode);

        $this->viewHelper->expects($this->atLeastOnce())->method('hasArgument')->with('else')->will($this->returnValue(true));
        $this->arguments['else'] = 'ElseArgument';
        $this->injectDependenciesIntoViewHelper($this->viewHelper);

        $actualResult = $this->viewHelper->_call('renderElseChild');
        $this->assertEquals('ElseArgument', $actualResult);
    }

    /**
     * @param array $arguments
     * @param mixed $expected
     * @test
     * @dataProvider getRenderStaticTestValues
     */
    public function testRenderStatic(array $arguments, $expected): void
    {
        $this->viewHelper->setArguments($arguments);
        $result = call_user_func_array(
            [$this->viewHelper, 'renderStatic'],
            [$arguments, function (): string {
                return '';
            }, new RenderingContextFixture()]
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRenderStaticTestValues(): array
    {
        return [
            'standard then argument' => [['condition' => true, 'then' => 'yes'], 'yes'],
            'then argument closure' => [['condition' => true, '__thenClosure' => function (): string {
                return 'yes';
            }], 'yes'],
            'standard else argument' => [['condition' => false, 'else' => 'no'], 'no'],
            'single else argument closure' => [['condition' => false, '__elseClosures' => [function (): string {
                return 'no';
            }]], 'no'],
            'else if closures first match true' => [
                [
                    'condition' => false,
                    '__elseClosures' => [
                        function (): string {
                            return 'first-else';
                        },
                        function (): string {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function (): bool {
                            return true;
                        },
                        function (): void {
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
                        function (): string {
                            return 'first-else';
                        },
                        function (): string {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function (): bool {
                            return false;
                        },
                        function (): bool {
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
                        function (): string {
                            return 'first-else';
                        },
                        function (): string {
                            return 'second-else';
                        }
                    ],
                    '__elseifClosures' => [
                        function (): bool {
                            return false;
                        },
                        function (): bool {
                            return false;
                        }
                    ]
                ],
                ''
            ],
        ];
    }
}
