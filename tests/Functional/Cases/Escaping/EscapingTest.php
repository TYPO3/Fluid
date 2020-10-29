<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MutableTestViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TestViewHelperResolver;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Rendering\RenderingContextFixture;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class EscapingTest
 */
class EscapingTest extends BaseFunctionalTestCase
{
    const UNESCAPED = '<script>alert(1)</script>';
    const ESCAPED = '&lt;script&gt;alert(1)&lt;/script&gt;';
    const UNESCAPED_WRAPPED = '<div><script>alert(1)</script></div>';
    const ESCAPED_WRAPPED = '&lt;div&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;/div&gt;';
    const ESCAPED_WRAPPED_STATIC_PROTECTED = '<div>&lt;script&gt;alert(1)&lt;/script&gt;</div>';

    /**
     * @var array
     */
    protected $variables = ['settings' => ['test' => '<strong>Bla</strong>']];

    public function renderCode(ViewHelperInterface $mutableViewHelper, string $fluidCode)
    {
        $resolver = new TestViewHelperResolver();
        $resolver->overrideResolving($mutableViewHelper);

        $configuration = new Configuration();
        $configuration->addEscapingInterceptor(new Escape());

        $context = $this->getMockBuilder(RenderingContextFixture::class)->setMethods(['buildParserConfiguration'])->getMock();
        $context->expects($this->once())->method('buildParserConfiguration')->willReturn($configuration);
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

    public function testWithEscapingBehaviorsNullWithoutContentOrOutputArguments()
    {
        $viewHelper = new MutableTestViewHelper();
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value}'), 'Vanilla object accessor');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapingBehaviorsNullWithOutputArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument();
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag with variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');

        // TODO: possible undesired behavior, double encoded variable
        $this->assertSame('&lt;div&gt;&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt;&lt;/div&gt;', $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    public function testWithEscapingBehaviorsNullWithContentArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument();
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = null
     */

    public function testWithEscapChildrenFalseWithoutContentOrOutputArguments()
    {
        $viewHelper = (new MutableTestViewHelper())->withEscapeChildren(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapChildrenFalseWithContentArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument()->withEscapeChildren(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeChildrenFalseWithOutputArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeChildren(false);
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');

        // TODO: possible undesired behavior, double encoded variable
        $this->assertSame('&lt;div&gt;&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt;&lt;/div&gt;', $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = null
     * Escape output = false
     */

    public function testWithEscapOutputFalseWithoutContentOrOutputArguments()
    {
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<f:if condition="1">{value}</f:if>'), 'Tag child variable (f:if)');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> f:if(condition: 1)}'), 'Inline pass of variable (f:if)');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithContentArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument()->withEscapeOutput(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');

        // TODO: security case - argument must be escaped!
        #$this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(content: value)}'), 'Inline pass of variable');

        // TODO: inconsistent case - argument is quoted and is escaped, which does not happen if the argument is NOT quoted
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: "{value}")}'), 'Inline pass of variable');

        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithOutputArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeOutput(false);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');

        // TODO: inconsistent case - argument is quoted and is escaped, which does not happen if the argument is NOT quoted
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');

        // TODO: possibly undesired behavior, escapes argument though output should be unescaped / children should be escaped but value is not passed as child
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = true
     * Escape output = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenTrueWithoutContentOrOutputArguments()
    {
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithContentArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument()->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithOutputArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');

        // TODO: security case - argument must be escaped!
        #$this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');

        // TODO: inconsistent case - argument is quoted and is escaped, which does not happen if the argument is NOT quoted
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');

        // TODO: possibly undesired behavior, escapes argument though output should be unescaped and value is not passed as child
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenFalseWithoutContentOrOutputArguments()
    {
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithContentArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withContentArgument()->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithOutputArgument()
    {
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');

        // TODO: expected to break after introducing mandatory argument escaping on f:if then+else
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{f:if(condition: 1, then: value)}'), 'Inline output argument (f:if)');

        // TODO: possibly undesired behavior, escapes argument though output should be unescaped and value is not passed as child
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /**
     * @return array
     */
    public function getTemplateCodeFixturesAndExpectations()
    {
        return [
            'escapeChildren can be disabled in template' => [
                '{escapingEnabled=false}<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'escapeOutput can be disabled in template' => [
                '{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'Disabling escaping twice in template throws parsing exception' => [
                '{escapingEnabled=false}<test:escapeChildrenDisabledAndEscapeOutputEnabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputEnabled>{escapingEnabled=false}',
                [],
                [],
                [],
                'TYPO3Fluid\\Fluid\\Core\\Parser\\Exception'
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenEnabledAndEscapeOutputDisabled>',
                $this->variables,
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
                ['<strong>Bla</strong>'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => [
                '{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}',
                $this->variables,
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
                ['<strong>Bla</strong>'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does encode variable value (encoded before passed to VH)' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                $this->variables,
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
                ['<strong>Bla</strong>'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenEnabledAndEscapeOutputDisabled()}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => [
                '<test:escapeChildrenEnabledAndEscapeOutputDisabled content="{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: settings.test)}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with children, properly encodes variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled>{settings.test}</test:escapeChildrenDisabledAndEscapeOutputDisabled>',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with children, properly encodes variable value' => [
                '{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with argument, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument, does not encode variable value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with string, does not encode string value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'<strong>Bla</strong>\')}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does encode variable value (encoded before passed to VH)' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                $this->variables,
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
                ['<strong>Bla</strong>'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and children rendering, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{settings.test -> test:escapeChildrenDisabledAndEscapeOutputDisabled()}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Tag syntax with nested inline syntax and argument in inline, does not encode variable value' => [
                '<test:escapeChildrenDisabledAndEscapeOutputDisabled content="{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: settings.test)}" />',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
            ],
        ];
    }
}
