.. include:: /Includes.rst.txt

:navigation-title: Escaping

.. _escaping-syntax:

======================
Fluid Syntax: Escaping
======================

.. _escaping-behavior:

Escaping behavior in inline syntax
==================================

At a certain nesting level, single quotes :xml:`'` required for the
inline syntax need to be escaped with a backslash:

..  code-block:: xml

    <f:render partial="MyPartial" arguments="{
        myArgument: '{f:format.trim(value: \'{f:format.case(mode: \\\'upper\\\', value: \\\'{myVariable}\\\')}\')}',
    }" />



While escaping cannot be avoided in all cases, alternatives should always be
preferred to improve the readability of templates. Depending on the use cases, there
are different approaches to achieve this.


Passing variables to inline ViewHelpers
---------------------------------------

If only a variable is passed as a ViewHelper argument, the single quotes :xml:`'`
and curly braces :xml:`{}` can be omitted:

..  code-block:: xml

    {f:format.case(mode: 'upper', value: myVariable)}


Using ViewHelper chaining if possible
-------------------------------------

Many ViewHelpers that perform changes on a single value also accept that value as a
child value. This allows a much cleaner syntax if you combine multiple ViewHelpers for
one value:

..  code-block:: xml

    {myVariable -> f:format.case(mode: 'upper') -> f:format.trim()}

.. _escaping-workarounds:

Workarounds for syntax collision with JS and CSS
================================================

While it is generally advisable to avoid inline JavaScript and CSS code within
Fluid templates, sometimes it may be unavoidable. This can lead to collisions
between Fluid's inline or variable syntax and the curly braces :xml:`{}`
syntax characters used in JavaScript and CSS.

Currently, there is no clean way to solve this due to limitations of Fluid's parser.
This would need a bigger rewrite, which is not yet feasible because of other more pressing
issues. However, there are workarounds that might be applicable in your use case.

f:format.json ViewHelper
------------------------

If your goal is to create JSON in your template, you can create an object in Fluid
and then use the :ref:`<f:format.json> ViewHelper <typo3fluid-fluid-format-json>`
to generate valid JSON:

..  code-block:: xml

    <div data-value="{f:format.json(value: {foo: 'bar', bar: 'baz'})}">


This can also be used directly in JavaScript code:

..  code-block:: xml

    <script>
    let data = {f:format.json(value: {foo: 'bar', bar: 'baz'}) -> f:format.raw()};


f:comment ViewHelper
--------------------

In some cases, you can use the :ref:`<f:comment> ViewHelper <typo3fluid-fluid-comment>`
to trap the Fluid parser:

..  code-block:: xml

    <f:variable name="test" value="bar" />
    <div
        x-data="{
            test: null,
            init() {
                test = 'foo';
            }
        }"
    >
        <f:comment>Only necessary to fix parser issue</f:comment>
        {test}
    </div>


f:format.raw ViewHelper
-----------------------

Within your JavaScript or CSS code, you can use the
:ref:`<f:format.raw> ViewHelper <typo3fluid-fluid-format-raw>`
on individual curly braces to trap the Fluid parser into ignoring that
character:

..  code-block:: xml

    <f:variable name="color" value="red" />
    <style>
        @media (min-width: 1000px) <f:format.raw>{</f:format.raw>
            p {
                background-color: {color};
            }
        }
    </style>


Using variables for curly braces
--------------------------------

You can define variables for curly braces to prevent parsing by the Fluid parser:

..  code-block:: xml

    <f:alias map="{l: '{', r: '}'}">
        var values = {};
        <f:for each="{items}" as="item">
            if (!values[{item.key}]) { values[{item.key}] = {}; }
            values[{item.key}][values[{item.key}].length] = {l} label: "{item.description} ({item.short})", value: {item.id} {r};
        </f:for>
    </f:alias>

Source: https://stackoverflow.com/a/51499855
