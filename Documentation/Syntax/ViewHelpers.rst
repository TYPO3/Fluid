.. include:: /Includes.rst.txt

:navigation-title: ViewHelpers

.. _viewhelpers-syntax:

=========================
Fluid Syntax: ViewHelpers
=========================

ViewHelper calls can be written using two distinct syntaxes—tag syntax and inline syntax.

ViewHelpers are grouped into namespaces and can be organized in folders.

When a ViewHelper is called in a template, you specify the namespace identifier, followed
by a colon and the path to the ViewHelper, separated by dots:

..  code-block::

    namespace:folder.subFolder.name

..  _viewhelper-tag-notation:

Tag syntax
==========

The tag syntax works just like HTML or XML. Tags can either be self-closing or can have
a content, which can include other HTML tags or ViewHelper calls.

..  code-block:: xml

    <!-- self-closing -->
    <f:constant name="\Vendor\Package\Class::CONSTANT" />

    <!-- text content -->
    <f:format.case mode="upper">lowercase</f:format.case>

    <!-- other ViewHelpers as content -->
    <f:switch expression="{myVariable}">
        <f:case value="foo">Variable is foo</f:case>
        <f:case value="bar">Variable is bar</f:case>
        <f:defaultCase>Variable is something else</f:defaultCase>
    </f:switch>

    <!-- Nested format ViewHelpers -->
    <f:format.trim>
        <f:replace search="foo" replace="bar">
            <f:format.case mode="upper">
                {myString}
            </f:format.case>
        </f:replace>
    </f:format.trim>

..  _viewhelper-inline-notation:

Inline syntax
=============

..  tip::

    There is an online tool to convert tag-based syntax to inline syntax:
    `Fluid Converter <https://fluid-to-inline-converter.com/>`__

The inline syntax works using curly braces `{}`. Most ViewHelpers can also be used with
the inline syntax, although the syntax might get more complicated depending on the use case.

..  code-block:: xml

    {f:constant(name: '\Vendor\Package\Class::CONSTANT')}

    {f:format.case(value: 'lowercase', mode: 'upper')}

    {myVariable -> f:format.case(mode: 'upper')}


ViewHelpers that operate on a singular input value can usually be chained, which can make
templates more readable:

..  code-block:: xml

    {myString -> f:format.case(mode: 'upper') -> f:replace(search: 'foo', replace: 'bar') -> f:format.trim()}


The inline syntax can also be indented and can contain trailing commas and whitespace:

..  code-block:: xml

    {f:render(
        partial: 'MyPartial',
        arguments: {
            foo: 'bar',
        },
    )}

..  _viewhelper-syntax-comparison:

ViewHelper syntax comparison
============================

Depending on the situation, it might be better to use the inline syntax instead of the
tag syntax and vice versa. Here is a more complex example that shows where the inline
syntax is still possible, but might make things more complicated:

.. code-block:: xml

    <f:variable name="myArray" value="{0: 'foo', 1: '', 2: 'bar'}" />
    <f:for each="{myArray}" as="item">
        <f:if condition="{item}">
            <f:render section="MySection" arguments="{item: item}" />
        </f:if>
    </f:for>

And its inline syntax variant:

.. code-block:: xml

    <f:variable name="myArray" value="{0: 'foo', 1: '', 2: 'bar'}" />
    {f:render(section:'MySection', arguments: {item: item}) -> f:if(condition: item) -> f:for(each: myArray, as: 'item')}

Please note that, in chained inline notation, the `f:if` ViewHelpers should not
use their usual `then` or `else` attributes, as they would directly output
their value and thus break the chain!


..  _viewhelper-namespaces-syntax:

ViewHelper namespaces
=====================

There are two syntax variants to import a ViewHelper namespace into a template.
In the following examples, `blog` is the namespace available within the Fluid template and
`MyVendor\BlogExample\ViewHelpers` is the PHP namespace to import into Fluid.

By default, the `f` namespace is predefined by Fluid. Depending on your setup,
additional global namespaces, defined directly via the
:ref:`ViewHelperResolver <viewhelperresolver>`, might
be available.

HTML tag syntax with xmlns attribute
------------------------------------

..  code-block:: xml

    <html
        xmlns:blog="http://typo3.org/ns/Myvendor/MyExtension/ViewHelpers"
        data-namespace-typo3-fluid="true"
    >
    </html>

This is useful for various IDEs and HTML autocompletion. The :html:`<html>`
element itself will not be rendered if the attribute
:html:`data-namespace-typo3-fluid="true"` is specified.

The namespace is built using the fixed `http://typo3.org/ns/` prefix followed
by the vendor name, package name, and the fixed `ViewHelpers` suffix.

..  important::
    Do not use `https://typo3.org/` (HTTPS instead of HTTP). Fluid would not be
    able to detect this namespace to convert it to the PHP class name prefixes.
    Remember: This is a unique XML namespace, it does not need to contain a valid URI.

Curly braces syntax
-------------------

..  code-block:: xml

    {namespace blog=MyVendor\BlogExample\ViewHelpers}

Any line that uses the curly braces syntax results in a blank line. Multiple
statements can be on either one line or across multiple lines.
