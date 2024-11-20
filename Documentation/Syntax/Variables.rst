.. include:: /Includes.rst.txt

:navigation-title: Variables

.. _variables-syntax:

=======================
Fluid Syntax: Variables
=======================

.. _variable-access:

Accessing variables
===================

Variables in Fluid can be accessed with the following syntax:

..  code-block:: html

    <h1>{title}</h1>

..  _variable-access-objects:

Arrays and objects
------------------

Use the dot ``.`` to access array keys:

..  code-block:: html

    <p>{data.0}, {data.1}</p>

This also works for object properties:

..  code-block:: html

    <p>{product.name}: {product.price}</p>

..  _dynamic-properties:

Dynamic keys/properties
-----------------------

It is possible to access array or object values by a dynamic index:

..  code-block:: html

    {myArray.{myIndex}}

.. _reserved-variables:

Reserved variable names
=======================

The following variable names are reserved and may not be used:

*   `_all`
*   `true`
*   `false`
*   `null`
