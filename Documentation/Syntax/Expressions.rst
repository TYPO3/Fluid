.. include:: /Includes.rst.txt

:navigation-title: Expressions

.. _expressions-syntax:

=========================
Fluid Syntax: Expressions
=========================

The syntax for expressions in Fluid is a mix between plain
variable access (e. g. `{variable}`) and ViewHelper usage in inline mode
(e.g. `{myArray -> f:count()}`).

Fluid's expression syntax is extendable, but in most circumstances the
following features are available. See
:ref:`Expression Nodes <creating-expressionnodes>`
to learn how to extend Fluid's expression behavior.

.. _objects-syntax:

Objects and arrays
==================

Within a ViewHelper call, arrays (with numeric keys) and object structures can be
defined inline:

..  code-block:: xml

    <f:variable name="myArray" value="{0: 'first item', 1: 'second item'}" />

    <f:variable name="myObject" value="{abc: 'first item', def: 'second item'}" />

    <f:variable name="myOtherObject" value="{'abc 123': 'first item', 'def 456': 'second item'}" />

These can also be nested and indented:

..  code-block:: xml

    <f:variable name="myArrayOfObjects" value="{
        0: {label: 'first item'},
        1: {label: 'second item'},
    }" />

Trailing commas are valid syntax and are recommended to create cleaner diffs in
version control systems.

.. _type-casting:

Type casting
============

The `as` expression can be used to convert variables to a different type.
For example, `{myPossiblyArray as array}` will ensure that `{myPossiblyArray}`
is accessed as an array even if it is of a different type such as :php:`null`, which is
useful if you are passing a value to a ViewHelper like :ref:`<f:for> <typo3fluid-fluid-for>`
that requires an array as input. Other supported types for casting are: `integer`,
`boolean`, `string`, `float` and `DateTime`.

If you use `as array` on a string that contains comma-separated values, the
string is split at each comma, similar to PHP's
`explode <https://www.php.net/manual/en/function.explode.php>`__ function.

.. _math-expressions:

Math expressions
================

Fluid supports basic math operations. For `myNumber = 6`, the following expressions
result in:

..  code-block:: xml

    {myNumber + 3} <!-- result: 9 -->
    {myNumber - 3} <!-- result: 3 -->
    {myNumber * 3} <!-- result: 18 -->
    {myNumber / 3} <!-- result: 2 -->
    {myNumber % 3} <!-- result: 0 -->
    {myNumber ^ 3} <!-- result: 216 -->

.. _ternary-expressions:

Ternary expressions
===================

Fluid supports the ternary operator for variables, allowing you to switch
between two variables based on the value of a variable. For static values,
like a string, this is **not** supported.

..  code-block:: xml

    {checkVariable ? thenVariable : elseVariable}

If `{checkVariable}` evaluates to :php:`true`, variable `{thenVariable}` is
used, otherwise variable `{elseVariable}` is used.
