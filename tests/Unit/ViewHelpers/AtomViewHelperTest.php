<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Tests\Unit\ViewHelpers;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Component\Error\ChildNotFoundException;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\EntryNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\ViewHelpers\AtomViewHelper;

/**
 * Testcase for AtomViewHelper
 */
class AtomViewHelperTest extends ViewHelperBaseTestCase
{
    /**
     * @test
     */
    public function rethrowsChildNotFoundWhenResolvingFails(): void
    {
        $subject = new AtomViewHelper();
        $errorContext = new RenderingContextFixture();
        $errorViewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['resolveAtom'])->disableOriginalConstructor()->getMock();
        $errorViewHelperResolver->expects($this->once())->method('resolveAtom')->willThrowException(new ChildNotFoundException('foo'));
        $errorContext->setViewHelperResolver($errorViewHelperResolver);
        $this->setExpectedException(ChildNotFoundException::class);
        $subject->getArguments()->assignAll(['atom' => 'my:atom']);
        $subject->evaluate($errorContext);
    }

    /**
     * @test
     */
    public function allowsArbitraryArguments(): void
    {
        $subject = new AtomViewHelper();
        $this->assertSame(true, $subject->allowUndeclaredArgument('foobar'));
        $this->assertSame(true, $subject->allowUndeclaredArgument('arbitrary'));
        $this->assertSame(true, $subject->allowUndeclaredArgument('--anything-goes--'));
    }

    public function getStandardTestValues(): array
    {
        $atom = (new EntryNode())->addChild(new TextNode('bar'))->addChild((new EntryNode())->setName('section')->addChild(new TextNode('section-content')));
        $context = new RenderingContextFixture();
        $viewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['resolveAtom'])->disableOriginalConstructor()->getMock();
        $viewHelperResolver->expects($this->any())->method('resolveAtom')->willReturn($atom);
        $context->setViewHelperResolver($viewHelperResolver);

        $errorContext = new RenderingContextFixture();
        $errorViewHelperResolver = $this->getMockBuilder(ViewHelperResolver::class)->setMethods(['resolveAtom'])->disableOriginalConstructor()->getMock();
        $errorViewHelperResolver->expects($this->once())->method('resolveAtom')->willThrowException(new ChildNotFoundException('foo'));
        $errorContext->setViewHelperResolver($errorViewHelperResolver);
        return [
            'resolves Atom by name provided in argument' => ['bar', $context, ['atom' => 'my:atom']],
            'resolves Atom by name provided in argument and renders section within atom' => ['section-content', $context, ['atom' => 'my:atom', 'section' => 'section']],
            'parses file name provided in argument' => [PHP_EOL . PHP_EOL, $context, ['file' => __DIR__ . '/../../Fixtures/Atoms/testAtom.html']],
            'uses self if no arguments provided' => ['foo', $context, [], [new TextNode('foo')]],
            'outputs null if not found and optional' => [null, $errorContext, ['atom' => 'my:atom', 'optional' => true]],
        ];
    }
}
