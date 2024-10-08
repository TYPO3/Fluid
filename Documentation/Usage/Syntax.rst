.. include:: /Includes.rst.txt

.. _fluid-syntax:

================
The Fluid Syntax
================

Fluid has two main syntax formats - the **tag mode** and the
**inline/shorthand mode**. The tag mode like the name indicates is designed to
be used as a pseudo (X)HTML tag and the inline mode is designed to access
variables and be placed in for example HTML tag attributes without breaking
validation. The tag mode is purely for using ViewHelpers (which you can read
more about in :doc:`the documentation about using ViewHelpers </Usage/ViewHelpers>`.
And the inline mode is designed to access variables, use special Fluid
expressions and use ViewHelpers.

Tag- and inline modes
=====================

The difference between the two modes can be briefly described this way:

.. code-block:: xml

    <f:count>{myArray}</f:count>

Is the same as:

.. code-block:: xml

    {myArray -> f:count()}

Which means that the variable `{myArray}` is in both cases passed to the
ViewHelper `f:count` (which counts arrays and outputs the number of items). In
the first line, tag mode is used and the variable is placed as tag content -
in the second line, inline mode is used and the `->` indicates that we want the
variable passed in exactly the same way as when using tag content.

This is very useful when you need to use the output of ViewHelpers in HTML tag
attributes:

Consider the following **bad** (X)HTML syntax:

.. code-block:: xml

    <ol class="item-count-<f:count>{firstArray}</f:count>">
        // render list
    </ol>

And the following **good** (X)HTML syntax we can get by using inline mode:

.. code-block:: xml

    <ol class="item-count-{firstArray -> f:count()}">
        // render list
    </ol>

The special `->` operator can be used to express any depth of ViewHelper calls.
This is called a "chain" and works like this:

.. code-block:: xml

    <f:format.trim>
        <f:replace search="foo" replace="bar">
            <f:format.case mode="upper">
                {myString}
            </f:format.case>
        </f:replace>
    </f:format.trim>

Can also be expressed like:

.. code-block:: xml

    {myString -> f:format.case(mode:'upper') -> f:replace(search:'foo',replace:'bar') -> f:format.trim()}

The latter syntax is quicker to parse but obviously does not allow for inserting
any (X)HTML elements around the single ViewHelper calls.

Examples with control flow structures
-------------------------------------

Here's some inline examples of how to include `f:if` or `f:for` ViewHelpers in
a chain:

.. code-block:: xml

    <f:variable name="myCondition" value="0" />
    <f:variable name="myVar" value="myValue" />
    {myVar -> f:if(condition: myCondition)} <!-- doesn't out variable -->

    <f:variable name="myCondition" value="1" />
    <f:variable name="myVar" value="myValue" />
    {myVar -> f:if(condition: myCondition)} <!-- outputs variable -->

    <f:variable name="items" value="{0:'item1',1:'item2'}" />
    {item -> f:for(each: items, as: 'item')} <!-- outputs items -->

Here's another more complex example:

.. code-block:: xml

    <f:variable name="myArray" value="{0:'foo',1:'',2:'bar'}" />
    <f:for each="{myArray}" as="item">
        <f:if condition="{item}">
            <f:render section="MySection" arguments="{item:item}" />
        </f:if>
    </f:for>

And its inline syntax variant:

.. code-block:: xml

    <f:variable name="myArray" value="{0:'foo',1:'',2:'bar'}" />
    {f:render(section:'MySection',arguments:'{item:item}') -> f:if(condition:item) -> f:for(each:myArray,as:'item')}

Please note that in chained inline notation the `f:if` ViewHelpers should not
have their usual `then` or `else` attributes, for them would directly output
their value and thus break the chain!

Expressions
===========

The **expressions** you can use in Fluid are a sort of mix between plain
variable access (e.g. `{variable}`) and ViewHelper usage in inline mode
(e.g. `{myArray -> f:count()}`). Expressions are written as
*variable access with additional syntax*:

* `{myPossiblyArray as array}` will for example make sure you access
  `{myPossiblyArray}` as an array even if it is :php:`null` or other, which is useful
  when you pass a suspect value to ViewHelpers like `f:for` which require
  arrays. Other cast types are: `integer`, `boolean`, `string`, `float` and `DateTime`.
* `{checkVariable ? thenVariable : elseVariable}` will for example output the
  variable `{thenVariable}` if `{checkVariable}` evaluates to :php:`true`, otherwise
  output the variable `{elseVariable}`.
* `{myNumber + 3}` (and other mathematical operations) will for example output
  the sum of `{myNumber}` plus `3`.

The **expressions** that are available when you render a template is purely
determined by the ViewHelperResolver (which you can read more about in
:ref:`the implementation chapter, section about ViewHelperResolver <implementation-view-helper-resolver>`.
You can `view the built-in expression types <https://github.com/TYPO3/Fluid/tree/main/src/Core/Parser/SyntaxTree/Expression>`__
at any time, but if you use Fluid through a framework please consult the
documentation for that framework to know which of the native **expressions**
are available as well as which custom ones may have been added by the framework.

Variables and types
===================

When using Fluid the standard PHP data types are used by ViewHelpers and the
engine itself - but when writing Fluid templates you don't always have the
option of *assigning a properly typed variable like :php:`false` that you can use when
a ViewHelper wants a boolean value*, which would be the strict way of passing a
boolean. To accommodate this, Fluid will convert compatible types into the
expected type when you call ViewHelpers:

.. code-block:: xml

    <f:if condition="1">
        This is true
    </f:if>

In this example, the `condition` argument expects a boolean value but we pass an
integer `1`. Internally, Fluid converts this (and any other compatible types
including string values of `true` or `false`) into proper booleans.

You can in most cases also use the casting expression (`{variable as boolean}`)
to ensure the correct data type. There are cases where you don't have the option
of neither assuming that Fluid will convert your value nor casting it - this
case being in arrays:

.. code-block:: xml

    <f:for each="{0: myVariable, 1: myOtherVariable}" as="newVariable">
        // render
    </f:for>

In these cases you cannot cast or convert the `myVariable` or `myOtherVariable`
variables - and the code inside `//render` may fail if you receive unexpected
types. To be able to cast a variable in this case, simply wrap it with quotes:

.. code-block:: xml

    <f:for each="{0: '{myVariable as integer}', 1: '{myOtherVariable as integer}'}" as="newVariable">
        // render
    </f:for>

...and Fluid will be able to detect the **expression** you used, extract and
cast the variable and finally remove the quotations and use the variable
directly. Semantically, the quotes mean you create a new `TextNode` that
contains a variable converted to the specified type.

Variable Scopes
===============

Each Fluid template, partial and section has its own variable scope. For templates,
these variables are set via the PHP API, for partials and sections the `<f:render>`
ViewHelper has a `arguments` argument to provide variables.

Inside templates, partials and sections there are two variable scopes: global
variables and local variables. Local variables are created by ViewHelpers that
provide additional variables to their child nodes. Local variables are only valid
in their appropriate context and don't leak out to the whole template.

For example, `<f:alias>` and `<f:for>` create local variables:

.. code-block:: xml

    <f:for each="{items}" as="item">
        <!-- {item} is valid here -->
    </f:for>
    <!-- {item} is no longer valid here -->

    <f:alias map="{item: myObject.sub.item}">
        <!-- {item} is valid here -->
    </f:for>
    <!-- {item} is no longer valid here -->

If a global variable uses the same name as a local value, the state of the global
value will be restored when the local variable is invalidated:

.. code-block:: xml

    <f:variable name="item" value="global item" />
    <!-- {item} is "global item" -->
    <f:for each="{0: 'local item'}" as="item">
        <!-- {item} is "local item" -->
    </f:for>
    <!-- {item} is "global item" -->

If a variable is created in a local block, for example by using the `<f:variable>`
ViewHelper, that variable is treated as a global variable, so it will leak out of
the scope:

.. code-block:: xml

    <f:for each="{0: 'first', 1: 'second'}" as="item">
        <f:variable name="lastItem" value="{item}" />
    </f:for>
    <!-- {lastItem} is "second" -->

If a global variable is created inside a local scope and uses the same name as a local
variable, it will still leak out of the scope and will also be valid inside the scope:

.. code-block:: xml

    <f:for each="{0: 'first', 1: 'second'}" as="item">
        <!-- {item} is "first" or "second" -->
        <f:variable name="item" value="overwritten" />
        <!-- {item} is "overwritten" -->
    </f:for>
    <!-- {item} is "overwritten" -->
