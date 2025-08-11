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

.. _escaping-collisions:
.. _escaping-workarounds:

Avoiding syntax collision with JS and CSS
=========================================

While it is generally advisable to avoid inline JavaScript and CSS code within
Fluid templates, sometimes it may be unavoidable. This can lead to collisions
between Fluid's inline or variable syntax and the curly braces :xml:`{}`
syntax characters used in JavaScript and CSS.

There are two approaches how these collisions can be avoided:

.. _escaping-collisions-json:

f:format.json ViewHelper
------------------------

If your goal is to create JSON in your template, you can create an object in Fluid
and then use the :ref:`<f:format.json> ViewHelper <typo3fluid-fluid-format-json>`
to generate valid JSON:

..  code-block:: xml

    <div data-value="{f:format.json(value: {foo: 'bar', bar: 'baz'})}">


This can also be used directly in JavaScript code that doesn't use any curly braces
itself:

..  code-block:: xml

    <script>
    let data = {f:format.json(value: {foo: 'bar', bar: 'baz'}) -> f:format.raw()};

.. _escaping-collisions-cdata:

CDATA sections
--------------

..  versionadded:: Fluid 5.0
    CDATA sections can be used to avoid syntax collisions. For Fluid 4 and below,
    workarounds are documented in older versions of this documentation.

CDATA sections can be used in Fluid templates to partially switch off the Fluid
parser and to alter the syntax for variables, expressions and inline ViewHelpers.
This makes it possible to mix CSS or JS with certain Fluid features.

The following syntax rules apply to text wrapped with :xml:`<![CDATA[ ]]>` in
Fluid templates:

*   ViewHelper tag syntax is ignored: ViewHelper tags will not be interpreted and
    will just remain as-is.
*   ViewHelper inline syntax, variables and expressions are only interpreted if
    they are using three curly braces `{{{ }}}` instead of just one `{ }`.
*   The :xml:`<![CDATA[ ]]>` keyword will be removed. If you want the CDATA to remain
    in the output, consider using the
    :ref:`<f:format.cdata> ViewHelper <typo3fluid-fluid-format-cdata>`.

Note that there might still be syntax overlaps if your CSS or JS uses three
curly braces that shouldn't be interpreted by Fluid.

Examples:

..  code-block:: xml
    :caption: Avoiding collisions in HTML attribute
    :emphasize-lines: 2, 11, 13

    <f:variable name="test" value="bar" />
    <![CDATA[
    <div
        x-data="{
            test: null,
            init() {
                test = 'foo';
            }
        }"
    >
        {{{test}}}
    </div>
    ]]>

..  code-block:: xml
    :caption: Avoiding collisions in inline CSS
    :emphasize-lines: 3, 6, 9

    <f:variable name="color" value="red" />
    <style>
    <![CDATA[
        @media (min-width: 1000px) {
            p {
                background-color: {{{color}}};
            }
        }
    ]]>
    </style>

..  code-block:: xml
    :caption: Avoiding collisions in inline JS
    :emphasize-lines: 6, 8, 10

    <f:variable name="countries" value="{
        0: {key: 'de', name: 'Germany', short: 'DE'},
        1: {key: 'us', name: 'United States of America', short: 'US'},
    }" />
    <script>
    <![CDATA[
        const settings = {
            countries: {{{countries -> f:format.json() -> f:format.raw()}}},
        };
    ]]>
    </script>
