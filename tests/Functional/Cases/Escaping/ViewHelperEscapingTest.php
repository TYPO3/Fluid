<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Tests\BaseTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\TestViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MutableTestViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TagBasedTestViewHelper;
use TYPO3Fluid\Fluid\View\TemplateView;

class ViewHelperEscapingTest extends BaseTestCase
{
    private const UNESCAPED = '<script>alert(1)</script>';
    private const ESCAPED = '&lt;script&gt;alert(1)&lt;/script&gt;';
    private const UNESCAPED_WRAPPED = '<div><script>alert(1)</script></div>';
    private const ESCAPED_WRAPPED = '&lt;div&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;/div&gt;';
    private const ESCAPED_WRAPPED_STATIC_PROTECTED = '<div>&lt;script&gt;alert(1)&lt;/script&gt;</div>';

    private function renderCode(ViewHelperInterface $mutableViewHelper, string $fluidCode): string
    {
        $resolver = new TestViewHelperResolver();
        $resolver->overrideResolving($mutableViewHelper);

        $configuration = new Configuration();
        $configuration->addEscapingInterceptor(new Escape());

        $context = $this->getMockBuilder(RenderingContext::class)->onlyMethods(['buildParserConfiguration'])->getMock();
        $context->expects(self::once())->method('buildParserConfiguration')->willReturn($configuration);
        $context->getTemplateParser()->setRenderingContext($context);
        $context->getTemplateCompiler()->setRenderingContext($context);
        $context->setVariableProvider(new StandardVariableProvider());
        $context->setViewHelperResolver($resolver);
        $context->getVariableProvider()->add('value', '<script>alert(1)</script>');

        $view = new TemplateView($context);
        $view->getTemplatePaths()->setTemplateSource($fluidCode);

        return $view->render();
    }

    /*
     * Escape children = null
     * Escape output = null
     */

    public function testWithEscapingBehaviorsNullWithoutContentOrOutputArguments(): void
    {
        $viewHelper = new MutableTestViewHelper();
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value}'), 'Vanilla object accessor');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapingBehaviorsNullWithOutputArgument(): void
    {
        // When both escapeOutput and escapeChildren are null, output escaping is enabled and children escaping is disabled (because output is escaped there is no need to escsape children explicitly).
        // The case therefore escapes both static HTML and variables/inline in arguments.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument();
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag with variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    public function testWithEscapingBehaviorsNullWithContentArgument(): void
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument();
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = null
     * Escape argument = null
     */

    public function testWithEscapChildrenFalseWithoutContentOrOutputArguments(): void
    {
        $viewHelper = (new MutableTestViewHelper())->withEscapeChildren(false);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapChildrenFalseWithContentArgument(): void
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument()->withEscapeChildren(false);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeChildrenFalseWithOutputArgument(): void
    {
        // Output argument is not escaped (it is not a content argument) because output escaping is OFF. Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeChildren(false);
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = null
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithoutContentOrOutputArguments(): void
    {
        // Child content and content argument are treated the same based on escape-children state. Children escaping is ON because escape output is OFF and escape children has no decision.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<f:if condition="1">{value}</f:if>'), 'Tag child variable (f:if)');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> f:if(condition: 1)}'), 'Inline pass of variable (f:if)');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithContentArgument(): void
    {
        // Child content and content argument are treated the same based on escape-children state. Children escaping is ON because escape output is OFF and escape children has no decision.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: value)}'), 'Inline variable in argument');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: "{value}")}'), 'Inline pass of variable, quoted');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithOutputArgument(): void
    {
        // Output argument is not escaped (it is not a content argument) because output escaping is OFF. Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false);
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = true
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenTrueWithoutContentOrOutputArguments(): void
    {
        // Output is escaped because children are escaped. Children are escaped because escape-output is OFF and no explicit decision is made for escape-children, causing escape-children to be ON.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(true);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithContentArgument(): void
    {
        // Child content is escaped because escape-output is OFF but an explicit decision for escape-children has been made to turn it ON.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false)->withEscapeChildren(true);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithOutputArgument(): void
    {
        // Output argument is not escaped (it is not a content argument) because escape-output is OFF and escape-children is not considered (it is an argument, not a child). Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false)->withEscapeChildren(true);
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenFalseWithoutContentOrOutputArguments(): void
    {
        // Nothing is escaped because all escaping is off.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithContentArgument(): void
    {
        // Child variable is not escaped because all escaping is off, including specific argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithOutputArgument(): void
    {
        // Argument is not escaped because all escaping is off, including specific argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{f:if(condition: 1, then: value)}'), 'Inline output argument (f:if)');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = null
     */

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentNullWithContentArgument(): void
    {
        // All escaping is off, output will not be escaped
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(null)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentNullWithOutputArgument(): void
    {
        // All escaping is off, output will not be escaped
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(null)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = true
     */

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentTrueWithContentArgument(): void
    {
        // Child variable is not escaped because both output and child escaping is off.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(true)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentTrueWithOutputArgument(): void
    {
        // Output argument is escaped despite both escape-output and escape-children being OFF, because argument was explicitly requested to be escaped.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(true)->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        self::assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = true
     * Escape output = false
     * Escape arguments = null
     */

    public function testEscapeContentArgumentWithEscapeChildrenTrueWithEscapeOutputOffEscapesArgument(): void
    {
        // Content argument is escaped because because escape-output is OFF but escape-children is ON and content argument is treated as child.
        $viewHelper = (new MutableTestViewHelper())->withEscapeChildren(true)->withEscapeOutput(false)->withContentArgument(null);
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: value)}'), 'Inline with content argument');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test content="{value}" />'), 'Tag with content argument');
        self::assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag with child variable');
    }

    /*
     * Disabling otherwise enabled escaping
     */

    public function testArgumentNotEscapedIfDisabledByFormatRawButNormallyWouldBeEscapedByOutputEscaping(): void
    {
        // Output is not escaped because VH is surrounded by f:format.raw, overriding escape-output, escape-children and escaping flag in ArgumentDefinition.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(true)->withEscapeChildren(true)->withContentArgument(true);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<f:format.raw>{value -> test:test()}</f:format.raw>'), 'Inline pass of variable surrounded by format.raw');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test() -> f:format.raw()}'), 'Inline pass of variable chained with format.raw');
    }

    public function testArgumentNotEscapedEvenIfArgumentRequestedEscapedBecauseChainingWithFormatRawOverridesArgumentEscaping(): void
    {
        // Content argument is not escaped, despite flag in ArgumentDefinition, because argument is chained with f:format.raw which overrides argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false)->withContentArgument(true);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test content="{value -> f:format.raw()}" />'), 'Tag with argument chained with format.raw');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(content: "{value -> f:format.raw()}")}'), 'Inline with argument chained with format.raw');
    }

    public function testArgumentNotEscapedIfDisabledByFormatRawButNormallyWouldBeEscapedByArgumentEscaping(): void
    {
        // Child is not escaped because VH is surrounded by f:format.raw, overriding escaping flag in ArgumentDefinition.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false);
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<f:format.raw>{value -> test:test()}</f:format.raw>'), 'Inline pass of variable surrounded by format.raw');
        self::assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test() -> f:format.raw()}'), 'Inline pass of variable chained with format.raw');
    }

    /*
     * TagBasedViewHelper attribute escaping
     */

    public function testTagBasedViewHelperEscapesAttributes(): void
    {
        // Tag ViewHelper attributes are always escaped; the only way to disable this escaping is for the VH to manually add the attribute and explicitly disable conversion of special HTML chars.
        $viewHelper = new TagBasedTestViewHelper();
        self::assertSame('<div class="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test class="{value}" />'), 'Tag attribute is escaped');
        self::assertSame('<div data-foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test data="{foo: value}" />'), 'Tag data attribute values are escaped');
        self::assertSame('<div foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test additionalAttributes="{foo: value}" />'), 'Tag additional attributes values are escaped');
        self::assertSame('<div data-&gt;' . self::ESCAPED . '&lt;="1" />', $this->renderCode($viewHelper, '<test:test data=\'{"><script>alert(1)</script><": 1}\' />'), 'Tag data attribute keys are escaped');
        self::assertSame('<div &gt;' . self::ESCAPED . '&lt;="1" />', $this->renderCode($viewHelper, '<test:test additionalAttributes=\'{"><script>alert(1)</script><": 1}\' />'), 'Tag additional attributes keys are escaped');

        // Disabled: bug detected, handleAdditionalArguments on AbstractTagBasedViewHelper does assign the tag attribute, but following this call,
        // the initialize() method is called which resets the TagBuilder and in turn removes the data- prefixed attributes which are then not re-assigned.
        // Regression caused by https://github.com/TYPO3/Fluid/pull/419.
        //$this->assertSame('<div data-foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test data-foo="{value}" />'), 'Tag unregistered data attribute is escaped');
    }
}
