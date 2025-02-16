.. include:: /Includes.rst.txt

:navigation-title: Conditions & Booleans

.. _conditions-syntax:

===================================
Fluid Syntax: Conditions & Booleans
===================================

..  _boolean-conditions:

Boolean conditions
==================

Boolean conditions are expressions that evaluate to true or false.

Boolean conditions can be used as ViewHelper arguments, whenever the datatype
`boolean` is given, e.g. in the `condition` argument of the
:ref:`<f:if> ViewHelper <typo3fluid-fluid-if>`.

1.  The expression can be a variable, which is evaluated as follows:

    *   Number: Evaluates to `true` if it is *not* `0`.
    *   Array: Evaluates to `true` if it contains at least one element.

2.  The expression can be a statement consisting of: `term1 operator term2`, for
    example `{variable} > 3`.

    *   The operator can be one of the following: `>`, `>=`, `<`, `<=`,
        `==`, `===`, `!=`, `!==`, or `%`.

3.  The previous expressions can be combined with `||` (logical OR) or `&&` (logical AND).


Examples:

..  code-block:: xml

    <f:if condition="{myObject}">
      ...
    </f:if>

    <f:if condition="{myNumber} > 3 || {otherNumber} || {somethingElse}">
       <f:then>
          ...
       </f:then>
       <f:else>
          ...
       </f:else>
    </f:if>

    <my:custom showLabel="{myString} === 'something'">
      ...
    </my:custom>


Example using the inline notation:

..  code-block:: xml

    <div class="{f:if(condition: blog.posts, then: 'blogPostsAvailable', else: 'noPosts')}">
      ...
    </div>

..  _boolean-literals:

Boolean literals
================

..  versionadded:: Fluid 4.0
    The boolean literals `{true}` and `{false}` have been introduced.

You can use the boolean literals `{true}` and `{false}` in ViewHelper calls. This works
both in tag and inline syntax:

..  code-block:: xml

    <f:render section="MySection" optional="{true}" />

    {f:render(section: 'MySection', optional: true)}

If a ViewHelper argument is defined as `boolean`, it is also possible to provide
values of different types, which will then be implicitly converted to a boolean:

.. code-block:: xml

    <f:render section="MySection" optional="1" />

This can be used to remain compatible to Fluid 2, which did not support boolean literals
in all cases.
