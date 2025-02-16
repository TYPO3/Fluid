.. include:: /Includes.rst.txt

:navigation-title: XSD Schema Files


.. _xsd-schema:

===========================
ViewHelper XSD Schema Files
===========================

Fluid supports autocompletion of the special Fluid tags via the use of an XSD
schema - a standard feature of the XML toolchain which allows defining required
attributes, expected attribute types and more. Some IDEs support the mapping of
such XSD schemas to XML namespace URLs (:html:`xmlns="..."`) which you can include in
Fluid templates.

Fluid includes the necessary CLI command to generate such schema files for all
available ViewHelpers within your project:

..  code-block:: bash

    ./vendor/bin/fluid schema

If no other parameter is defined, the CLI command will create a schema file for
each available namespace in the current directory, for example:

..  code-block::

    schema_T3Docs_FluidDocumentationGenerator_ViewHelpers.xsd
    schema_TYPO3Fluid_Fluid_Tests_Functional_Fixtures_ViewHelpers.xsd
    schema_TYPO3Fluid_Fluid_Tests_Functional_ViewHelpers_StaticCacheable_Fixtures_ViewHelpers.xsd
    schema_TYPO3Fluid_Fluid_Tests_Unit_Schema_Fixtures_ViewHelpers.xsd
    schema_TYPO3Fluid_Fluid_ViewHelpers.xsd

You can specify a different destination directory by providing the `--destination`
argument. If the directory doesn't exist, it will be created:

..  code-block:: bash

    ./vendor/bin/fluid schema --destination schemas/
