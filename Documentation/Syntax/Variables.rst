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
