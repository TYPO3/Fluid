.. include:: /Includes.rst.txt

.. _what-are-viewhelpers:

===========
ViewHelpers
===========

.. _viewhelper-usage:

How to use ViewHelpers
======================

ViewHelpers are special tags in the template which provide more complex
functionality such as loops or special formatting of values. The functionality
of a ViewHelper is implemented in PHP, and each ViewHelper has its own PHP class.

See the :ref:`ViewHelper Reference <viewhelper-reference>` for a complete
list of all available ViewHelpers.

Within Fluid, the ViewHelper is used as a special HTML element with a namespace
prefix, for example the namespace prefix `f` is used for ViewHelpers from the
built-in Fluid namespace:

..  code-block:: xml

    <f:for each="{results}" as="result">
       <li>{result.title}</li>
    </f:for>

The `f` namespace is already defined, but can be explicitly specified to
improve IDE autocompletion.

Custom ViewHelpers use their own namespace, in this case `blog`:

..  code-block:: xml

    <blog:myViewHelper argument1="something" />

The namespace needs to be registered explicitly, see the next section.

ViewHelpers can accept input values both from their tag content and from arguments,
which are specified as tag attributes. The ViewHelper syntax is documented
in :ref:`Fluid ViewHelper Syntax <fluid-syntax-viewhelpers>`.

.. _viewhelper-namespaces:

Registering/importing ViewHelpers
=================================

When you need to use third-party ViewHelpers in your templates, there are multiple
equally valid options.

You can use the PHP API to register a namespace that should be
available in *all template files* without further importing:

.. code-block:: php

    $view = new TemplateView();
    $view->getRenderingContext()->getViewHelperResolver()
        ->addNamespace('foo', 'Vendor\\Foo\\ViewHelpers');

To make a namespace only available in one template file, the following syntax
variants are possible:

.. code-block:: xml

    <!-- xmlns variant -->
    <html
        xmlns:foo="http://typo3.org/ns/Vendor/Foo/ViewHelpers"
        data-namespace-typo3-fluid="true"
    >

    <!-- inline variant -->
    {namespace foo=Vendor\Foo\ViewHelpers}

Once you have registered/imported the ViewHelper collection, you can start using
it in your templates via the namespace alias you used during registration (in this
example: `foo` is the alias name).

..  deprecated:: 4.2
    It was possible to define a namespace in a template and then use it in a
    referenced partial without redeclaring it in the partial. This behavior
    (inheritance of ViewHelper namespaces) is deprecated and will no longer
    work in Fluid 5.

.. _tagbased-viewhelpers:

Tag-based ViewHelpers
=====================

Tag-based ViewHelpers are special ViewHelpers that extend a different base class called
`AbstractTagBasedViewHelper <https://github.com/TYPO3/Fluid/blob/main/src/Core/ViewHelper/AbstractTagBasedViewHelper.php>`_.
The purpose of these special ViewHelpers is to generate a HTML tag based on the supplied
arguments and content.

Tag-based ViewHelpers provide default arguments that help enhancing the generated HTML
tag:

*   An array of `data-*` attributes can be provided via the `data` argument
*   An array of `aria-*` attributes can be provided via the `aria` argument
*   An array of additional HTML attributes can be provided via the `additionalAttributes`
    argument
*   You can also supply arbitrary arguments that don't need to be defined by the ViewHelper,
    which will be added to the generated HTML tag automatically

Example:

.. code-block:: xml

    <my:viewHelper
        data="{
            foo: 'data foo',
            bar: 'data bar',
        }"
        aria="{
            label: 'my label',
        }"
        additionalAttributes="{
            'my-attribute': 'my attribute value',
        }"
        another-attribute="my other value"
    >
        content
    </my:viewHelper>

Assuming that the ViewHelper is configured to create a :html:`<div>` tag,
this would be the result:

.. code-block:: html

    <div
        data-foo="data foo"
        data-bar="data bar"
        aria-label="my label"
        my-attribute="my attribute value"
        another-attribute="my other value"
    >
        content
    </div>

Boolean attributes
------------------

You can use the boolean literals `{true}` and `{false}` to enable or disable
attributes of tag-based ViewHelpers:

..  code-block:: xml

    <my:viewHelper async="{true}" />
    Result: <div async="async" />

    <my:viewHelper async="{false}" />
    Result: <div />

Of course, any variable containing a boolean can be supplied as well:

..  code-block:: xml

    <my:viewHelper async="{isAsync}" />

It is also possible to cast a string to a boolean:

..  code-block:: xml

    <my:viewHelper async="{myString as boolean}" />

For backwards compatibility, empty strings still lead to the attribute
being omitted from the tag:

..  code-block:: xml

    <f:variable name="myEmptyString" value="" />
    <my:viewHelper async="{myEmptyString}" />
    Result: <div />

.. _condition-viewhelpers:

Condition ViewHelpers
=====================

Condition ViewHelpers are another special type of ViewHelper that allow to check for certain
conditions within a template. They extend from a different base class called
`AbstractConditionViewHelper <https://github.com/TYPO3/Fluid/blob/main/src/Core/ViewHelper/AbstractConditionViewHelper.php>`_.

All condition ViewHelpers have in common that a `then` and one or multiple `else` clauses
can be defined. There are multiple ways to do this, and almost all combinations imaginable
are possible.

The generic and most used condition ViewHelper is :ref:`<f:if> <typo3fluid-fluid-if>`.

then/else as argument
---------------------

You can define `then` and `else` as ViewHelper arguments:

..  code-block:: xml

    <!-- then and else -->
    <f:if condition="{myVar} == 'test'" then="variable is test" else="variable is something else" />
    {f:if(condition: '{myVar} == \'test\'', then: 'variable is test', else: 'variable is something else')}

    <!-- only then -->
    <f:if condition="{myVar} == 'test'" then="variable is test" />
    {f:if(condition: '{myVar} == \'test\'', then: 'variable is test')}

    <!-- only else -->
    <f:if condition="{myVar} == 'test'" else="variable is something else" />
    {f:if(condition: '{myVar} == \'test\'', else: 'variable is something else')}

then/else as child ViewHelpers
------------------------------

With the tag syntax, it is also possible to define more advanced conditions:

..  code-block:: xml

    <!-- only then -->
    <f:if condition="{myVar} == 'test'">
        variable is test
    </f:if>

    <!-- then and else -->
    <f:if condition="{myVar} == 'test'">
        <f:then>variable is test</f:then>
        <f:else>variable is something else</f:else>
    </f:if>

    <!-- only else -->
    <f:if condition="{myVar} == 'test'">
        <f:else>variable is something else</f:else>
    </f:if>

    <!-- multiple else-if -->
    <f:if condition="{myVar} == 'test'">
        <f:then>variable is test</f:then>
        <f:else if="{myVar} == 'foo'">variable is foo</f:else>
        <f:else if="{myVar} == 'bar'">variable is bar</f:else>
        <f:else>variable is something else</f:else>
    </f:if>

Get verdict by omitting then/else
---------------------------------

..  versionadded:: Fluid 4.1

If neither `then` nor `else` in any of the accepted forms is specified, the ViewHelper
returns the verdict of the condition as boolean. This value can be used for further
processing in the template, for example in complex conditions:

..  code-block:: xml

    <!-- The variable will contain the result of the condition as boolean -->
    <f:variable
        name="isEitherTestOrFoo"
        value="{f:if(condition: '{myVar} == \'test\' || {myVar} == \'foo\'')}"
    />

    <!-- This example combines two custom condition ViewHelpers to a larger condition -->
    <f:if condition="{my:customCondition(value: variableToCheck)} || {my:otherCondition(value: variableToCheck)}">
        ...
    </f:if>

This syntax can also be helpful in combination with a
`Tag-Based ViewHelper <https://docs.typo3.org/permalink/fluid:tagbased-viewhelpers>`_:

..  code-block:: xml

    <!-- disabled attribute is set if either no first name or no last name is set -->
    <my:tagBased
        disabled="{f:if(condition: '!{firstName} || !{lastName}')}"
    />

.. _understanding-viewhelpers:

Understanding ViewHelpers
=========================

All built-in ViewHelpers are documented in the :ref:`ViewHelper Reference <viewhelper-reference>`.
If you want to learn more about a specific ViewHelper or if you are using a custom
ViewHelper that isn't documented, you can take a look at the ViewHelper source code, written
in PHP.

Each ViewHelper has a corresponding PHP file, which contains a class that describes the
ViewHelper's arguments as well as its behavior in the template. Such classes are usually placed
in the `Vendor\Package\ViewHelpers` PHP namespace (where `Vendor` and `Package` are placeholders
for actual values) and follow the following naming convention:

*   `f:format.raw` results from the PHP class :php:`TYPO3Fluid\Fluid\ViewHelpers\Format\RawViewHelper`
*   `f:render` results from the PHP class :php:`TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper`
*   `mypkg:custom.specialFormat` results from the PHP class
    :php:`My\Package\ViewHelpers\Custom\SpecialFormatViewHelper`, assuming you added
    `xmlns:mpkg="http://typo3.org/ns/My/Package/ViewHelpers"` or an alternative namespace
    registration (see above).

Note that these rules only apply to *normal* ViewHelpers. For :ref:`Components <_components-definition>`,
different rules apply.

The arguments a ViewHelper supports will be verbosely registered in the
`initializeArguments()` function of each ViewHelper class. Inspect this method to
see the names, types, descriptions, required flags and default values of all
attributes. An example argument definition looks like this:

.. code-block:: php

    public function initializeArguments() {
        $this->registerArgument('myArgument', 'boolean', 'If true, makes ViewHelper do foobar', false, false);
    }

Which translated to human terms means that we:

*   Register an argument named `myArgument`
*   Specify that it must be a boolean value or an expression resulting in a
    boolean value (see :ref:`Boolean conditions <boolean-conditions>`).
    Other valid types are `integer`, `string`, `float`, `array`, `object`, `DateTime` and
    other class names. The *array of* syntax can also be used, for example `string[]` or
    `Vendor\Package\MyClass[]`.
*   Describe the argument's behavior in simple terms.
*   Define that the argument is not required (the 4th argument is :php:`false`).
*   Set a default value of :php:`false` (5th argument), if the argument is not
    provided when calling the ViewHelper.

The ViewHelper itself would then be callable like this:

..  code-block:: xml

    <mypkg:custom.specialFormat myArgument="{true}">{someVariable}</mypkg:custom.specialFormat>

What the ViewHelper does with its input values is determined by the `render()` method in the ViewHelper class.
