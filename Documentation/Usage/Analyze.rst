.. include:: /Includes.rst.txt

:navigation-title: Checking templates

.. _analyze-templates:

=============================
Checking templates for errors
=============================

..  versionadded:: Fluid 5.3

The command `analyze` parses Fluid templates without rendering them. It
reports what it finds:

*   a syntax error, for example ViewHelper tags that are nested wrongly
*   a ViewHelper or a ViewHelper namespace that does not resolve
*   a variable name that starts with an underscore
*   a deprecated ViewHelper or ViewHelper argument, if the deprecation is
    triggered while the template is parsed, see
    :ref:`Deprecating a ViewHelper or an argument
    <creating-viewhelpers-deprecating>`

TYPO3 provides its own command for this purpose, see
`Check templates with the Fluid analyser
<https://docs.typo3.org/permalink/t3coreapi:fluid-analyze-templates>`_.

.. _analyze-templates-files:

Checking the templates in a directory
=====================================

Pass one or more directories with the argument `--path`. Separate several
directories with spaces:

..  code-block:: bash

    ./vendor/bin/fluid analyze --path templates/ partials/

The command checks the files with the file extension `*.fluid.*` in these
directories and their subdirectories. To check other files, name their file
extension with the argument `--extension`:

..  code-block:: bash

    ./vendor/bin/fluid analyze --path templates/ --extension html

To check a single file, use the argument `--template` instead of `--path`:

..  code-block:: bash

    ./vendor/bin/fluid analyze --template templates/List.fluid.html

The argument `--template` cannot be combined with `--path` or `--extension`.

.. _analyze-templates-output:

Reading the result
==================

The command writes each finding with the path of the template and the
message. The lines of the following example are wrapped:

..  code-block:: text

    [ERROR] templates/List.fluid.html: Fluid parse error in template
    templates/List.fluid.html, line 4 at character 1. Error: Variable
    identifiers cannot start with a "_": _temp (error code 1765900762).
    Template source chunk: {_temp}

    [DEPRECATION] templates/Detail.fluid.html: <my:obsolete> is deprecated
    and will be removed in version 6.

The parser stops at the first error in a template. Therefore, the command
reports at most one error per template. Fix the error and run the command
again to find the next one.

The command ends with the exit code `0`, also if it reports an error. To
stop a continuous integration pipeline on an error, check the output of the
command.

.. _analyze-templates-tools:

Checking a template from another tool
=====================================

With the argument `--stdin`, the command reads one template from the
standard input instead of a file. The file extension does not matter then.
With the argument `--json`, the command writes its result as JSON, including
the line and the character of each error. Editors and Git hooks can use both
arguments to check a single template:

..  code-block:: bash

    ./vendor/bin/fluid analyze --stdin --json < templates/List.fluid.html
