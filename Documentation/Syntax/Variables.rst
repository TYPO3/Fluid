.. include:: /Includes.rst.txt

:navigation-title: Variables

.. _variables-syntax:

=======================
Fluid Syntax: Variables
=======================

.. _variable-access:

Accessing variables
===================

Variables in Fluid can be accessed with the following braces :html:`{}` syntax:

..  code-block:: html

    <h1>{title}</h1>

..  _variable-access-objects:

Arrays and objects
------------------

Use the dot character :html:`.` to access array keys:

..  code-block:: html

    <p>{data.0}, {data.1}</p>

This also works for object properties:

..  code-block:: html

    <p>{product.name}: {product.price}</p>

These object properties are obtained by evaluating a fallback chain,
which includes various getter methods as well as direct property access.
For example, the following PHP-equivalents would be checked for `{product.name}`:

..  code-block:: php

    $product->getName()
    $product->isName()
    $product->hasName()
    $product->name

Also, both `ArrayAccess` and the PSR `ContainerInterface` are supported.

.. _variable-access-enums:

Enums
-----

..  versionadded:: Fluid 5.0

PHP enum cases can be output directly using :html:`{enum}` or
:html:`<f:constant>`. Backed enums
output their value: integer values become text, and string values are used
as-is. Unbacked enums output their case name. Normal HTML escaping applies.
Backed values such as :php:`0` and :php:`''` are preserved without falling
back to the case name.

Use :html:`{enum.name}` to explicitly access the case name, or
:html:`{enum.value}` to access the value of a backed enum. Enum cases remain
objects when passed as arguments to ViewHelpers or components.

Normal enum output always returns an HTML-escaped string, including when
the template consists only of :html:`{enum}`.

With :html:`{enum -> f:format.raw()}`, the enum object is preserved when
this expression is the template's only content. Adding text or whitespace
converts the enum to its backed value or case name and concatenates it
without HTML escaping.

..  _dynamic-properties:

Dynamic keys/properties
-----------------------

It is possible to access array or object values by a dynamic index:

..  code-block:: html

    {myArray.{myIndex}}

.. _reserved-variables:

Reserved variable names
=======================

The following variable names are **reserved** and *must not* be used:

*   `_all`
*   `true`
*   `false`
*   `null`

..  versionadded:: Fluid 5.0

Starting with Fluid 5, all variable names starting with an underscore are reserved
for internal purposes as well. Note that this only affects the primary variable name,
not the name of array keys or object properties.

..  code-block:: html

    <!-- invalid variable access -->
    {_temp}
    {_somethingElse}

    <!-- valid variable access -->
    {data._something}
    {myArray._myKey}
