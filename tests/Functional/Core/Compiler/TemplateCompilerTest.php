<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Core\Parser;

use PHPUnit\Framework\Attributes\Test;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\TextNode;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Tests\Functional\AbstractFunctionalTestCase;

final class TemplateCompilerTest extends AbstractFunctionalTestCase
{
    #[Test]
    public function wrapViewHelperNodeArgumentEvaluationInClosureCreatesExpectedString(): void
    {
        $renderingContext = new RenderingContext();
        $viewHelperNode = new ViewHelperNode($renderingContext, 'f', 'format.trim', ['value' => new TextNode('foo')]);
        $expected = 'function() use ($renderingContext) {' . chr(10);
        $expected .= chr(10);
        $expected .= 'return \'foo\';' . chr(10);
        $expected .= '}';
        $subject = new TemplateCompiler();
        self::assertEquals($expected, $subject->wrapViewHelperNodeArgumentEvaluationInClosure($viewHelperNode, 'value'));
    }
}
