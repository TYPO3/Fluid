.. include:: /Includes.rst.txt

.. _variables:

=========
Variables
=========

Assign a variable in PHP:

..  code-block:: php

    $this->view->assign('title', 'An example title');

Output it in a Fluid template:

..  code-block:: html

    <h1>{title}</h1>

The result:

..  code-block:: html

    <h1>An example title</h1>

In the template's HTML code, wrap the variable name into curly
braces to output it.

.. _variable-all:

Special _all Variable
=========================

The special variable `{_all}` contains an array with all variables that are currently
defined in your template. This can be helpful for debugging purposes, but also if you
want to pass all variables to a partial:

..  code-block:: xml

    <f:render partial="MyPartial" arguments="{_all}" />

However, be advised that this makes it more difficult to re-use partials, so it's recommend
to only pass the variables that are actually needed in the partial.

.. _variable-scopes:

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
