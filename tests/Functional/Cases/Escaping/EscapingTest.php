<?php
namespace TYPO3Fluid\Fluid\Tests\Functional\Cases\Escaping;

use TYPO3Fluid\Fluid\Core\Parser\Configuration;
use TYPO3Fluid\Fluid\Core\Parser\Interceptor\Escape;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Tests\Functional\BaseFunctionalTestCase;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\MutableTestViewHelper;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\ViewHelpers\TagBasedTestViewHelper;
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
        // When both escapeOutput and escapeChildren are null, output escaping is enabled and children escaping is disabled (because output is escaped there is no need to escsape children explicitly).
        // The case therefore escapes both static HTML and variables/inline in arguments.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument();
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag with variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
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
     * Escape argument = null
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
        // Output argument is not escaped (it is not a content argument) because output escaping is OFF. Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument()->withEscapeChildren(false);
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::ESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = null
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithoutContentOrOutputArguments()
    {
        // Child content and content argument are treated the same based on escape-children state. Children escaping is ON because escape output is OFF and escape children has no decision.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<f:if condition="1">{value}</f:if>'), 'Tag child variable (f:if)');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> f:if(condition: 1)}'), 'Inline pass of variable (f:if)');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithContentArgument()
    {
        // Child content and content argument are treated the same based on escape-children state. Children escaping is ON because escape output is OFF and escape children has no decision.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: value)}'), 'Inline variable in argument');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: "{value}")}'), 'Inline pass of variable, quoted');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithOutputArgument()
    {
        // Output argument is not escaped (it is not a content argument) because output escaping is OFF. Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = true
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenTrueWithoutContentOrOutputArguments()
    {
        // Output is escaped because children are escaped. Children are escaped because escape-output is OFF and no explicit decision is made for escape-children, causing escape-children to be ON.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithContentArgument()
    {
        // Child content is escaped because escape-output is OFF but an explicit decision for escape-children has been made to turn it ON.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenTrueWithOutputArgument()
    {
        // Output argument is not escaped (it is not a content argument) because escape-output is OFF and escape-children is not considered (it is an argument, not a child). Developer who wrote ViewHelper would be responsible for escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false)->withEscapeChildren(true);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: "{value}")}'), 'Inline output argument');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = false
     */

    public function testWithEscapOutputFalseWithEscapeChildrenFalseWithoutContentOrOutputArguments()
    {
        // Nothing is escaped because all escaping is off.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithContentArgument()
    {
        // Child variable is not escaped because all escaping is off, including specific argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(false)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithOutputArgument()
    {
        // Argument is not escaped because all escaping is off, including specific argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(false)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{f:if(condition: 1, then: value)}'), 'Inline output argument (f:if)');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = null
     */

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentNullWithContentArgument()
    {
        // All escaping is off, output will not be escaped
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(null)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentNullWithOutputArgument()
    {
        // All escaping is off, output will not be escaped
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(null)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = false
     * Escape output = false
     * Escape arguments = true
     */

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentTrueWithContentArgument()
    {
        // Child variable is not escaped because both output and child escaping is off.
        $viewHelper = (new MutableTestViewHelper())->withContentArgument(true)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::UNESCAPED_WRAPPED, $this->renderCode($viewHelper, '<test:test><div>{value}</div></test:test>'), 'Tag child variable with static HTML');
    }

    public function testWithEscapeOutputFalseWithEscapeChildrenFalseWithEscapeArgumentTrueWithOutputArgument()
    {
        // Output argument is escaped despite both escape-output and escape-children being OFF, because argument was explicitly requested to be escaped.
        $viewHelper = (new MutableTestViewHelper())->withOutputArgument(true)->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test output="{value}" />'), 'Tag child variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(output: value)}'), 'Inline output argument');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '<test:test output="<div>{value}</div>" />'), 'Tag with variable and static HTML');
        $this->assertSame(self::ESCAPED_WRAPPED_STATIC_PROTECTED, $this->renderCode($viewHelper, '{test:test(output: "<div>{value}</div>")}'), 'Inline with static HTML');
    }

    /*
     * Escape children = true
     * Escape output = false
     * Escape arguments = null
     */

    public function testEscapeContentArgumentWithEscapeChildrenTrueWithEscapeOutputOffEscapesArgument()
    {
        // Content argument is escaped because because escape-output is OFF but escape-children is ON and content argument is treated as child.
        $viewHelper = (new MutableTestViewHelper())->withEscapeChildren(true)->withEscapeOutput(false)->withContentArgument(null);
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{value -> test:test()}'), 'Inline pass of variable');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '{test:test(content: value)}'), 'Inline with content argument');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test content="{value}" />'), 'Tag with content argument');
        $this->assertSame(self::ESCAPED, $this->renderCode($viewHelper, '<test:test>{value}</test:test>'), 'Tag with child variable');
    }

    /*
     * Disabling otherwise enabled escaping
     */

    public function testArgumentNotEscapedIfDisabledByFormatRawButNormallyWouldBeEscapedByOutputEscaping()
    {
        // Output is not escaped because VH is surrounded by f:format.raw, overriding escape-output, escape-children and escaping flag in ArgumentDefinition.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(true)->withEscapeChildren(true)->withContentArgument(true);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<f:format.raw>{value -> test:test()}</f:format.raw>'), 'Inline pass of variable surrounded by format.raw');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test() -> f:format.raw()}'), 'Inline pass of variable chained with format.raw');
    }

    public function testArgumentNotEscapedEvenIfArgumentRequestedEscapedBecauseChainingWithFormatRawOverridesArgumentEscaping()
    {
        // Content argument is not escaped, despite flag in ArgumentDefinition, because argument is chained with f:format.raw which overrides argument escaping.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false)->withContentArgument(true);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<test:test content="{value -> f:format.raw()}" />'), 'Tag with argument chained with format.raw');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{test:test(content: "{value -> f:format.raw()}")}'), 'Inline with argument chained with format.raw');
    }

    public function testArgumentNotEscapedIfDisabledByFormatRawButNormallyWouldBeEscapedByArgumentEscaping()
    {
        // Child is not escaped because VH is surrounded by f:format.raw, overriding escaping flag in ArgumentDefinition.
        $viewHelper = (new MutableTestViewHelper())->withEscapeOutput(false)->withEscapeChildren(false);
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '<f:format.raw>{value -> test:test()}</f:format.raw>'), 'Inline pass of variable surrounded by format.raw');
        $this->assertSame(self::UNESCAPED, $this->renderCode($viewHelper, '{value -> test:test() -> f:format.raw()}'), 'Inline pass of variable chained with format.raw');
    }

    /*
     * TagBasedViewHelper attribute escaping
     */

    public function testTagBasedViewHelperEscapesAttributes()
    {
        // Tag ViewHelper attributes are always escaped; the only way to disable this escaping is for the VH to manually add the attribute and explicitly disable conversion of special HTML chars.
        $viewHelper = new TagBasedTestViewHelper();
        $this->assertSame('<div class="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test class="{value}" />'), 'Tag attribute is escaped');
        $this->assertSame('<div data-foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test data="{foo: value}" />'), 'Tag data attribute values are escaped');
        $this->assertSame('<div foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test additionalAttributes="{foo: value}" />'), 'Tag additional attributes values are escaped');
        $this->assertSame('<div data-&gt;' . self::ESCAPED . '&lt;="1" />', $this->renderCode($viewHelper, '<test:test data=\'{"><script>alert(1)</script><": 1}\' />'), 'Tag data attribute keys are escaped');
        $this->assertSame('<div &gt;' . self::ESCAPED . '&lt;="1" />', $this->renderCode($viewHelper, '<test:test additionalAttributes=\'{"><script>alert(1)</script><": 1}\' />'), 'Tag additional attributes keys are escaped');

        // Disabled: bug detected, handleAdditionalArguments on AbstractTagBasedViewHelper does assign the tag attribute, but following this call,
        // the initialize() method is called which resets the TagBuilder and in turn removes the data- prefixed attributes which are then not re-assigned.
        // Regression caused by https://github.com/TYPO3/Fluid/pull/419.
        //$this->assertSame('<div data-foo="' . self::ESCAPED . '" />', $this->renderCode($viewHelper, '<test:test data-foo="{value}" />'), 'Tag unregistered data attribute is escaped');
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
            'EscapeChildrenEnabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does not encode variable value' => [
                '{test:escapeChildrenEnabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
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
            'EscapeChildrenDisabledAndEscapeOutputDisabled: Inline syntax with argument in quotes, does not encode variable value' => [
                '{test:escapeChildrenDisabledAndEscapeOutputDisabled(content: \'{settings.test}\')}',
                $this->variables,
                ['<strong>Bla</strong>'],
                ['&lt;strong&gt;Bla&lt;/strong&gt;'],
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
