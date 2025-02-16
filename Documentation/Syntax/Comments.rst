.. include:: /Includes.rst.txt

:navigation-title: Comments

.. _comments-syntax:

======================
Fluid Syntax: Comments
======================

If you want to completely skip parts of your template, you can make use of
the :ref:` <f:comment> ViewHelper <typo3fluid-fluid-comment>`.

..  versionchanged:: Fluid 4.0
    The content of the :ref:`<f:comment> ViewHelper <typo3fluid-fluid-comment>` is removed
    before parsing. It is no longer necessary to combine it with CDATA tags
    to disable parsing.

..  code-block:: xml

    <f:comment>
        This will be ignored by the Fluid parser and will not appear in
        the source code of the rendered template
    </f:comment>

Since the content of the :ref:`<f:comment> ViewHelper <typo3fluid-fluid-comment>` is completely removed
before parsing, you can also use it to temporarily comment out invalid Fluid syntax while debugging:

..  code-block:: xml

    <f:comment>
        <x:someBrokenFluid>
    </f:comment>
