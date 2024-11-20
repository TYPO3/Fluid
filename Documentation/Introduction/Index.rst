.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

Fluid is a PHP-based templating engine for web projects. In contrast to other
templating engines, it uses an XML-based syntax in its templates, which allows
template authors to apply their existing HTML knowledge in Fluid templates.

Fluid originated in the TYPO3 and Neos ecosystem before it was extracted
from these projects into a separate PHP package. While its main usage nowadays
is within TYPO3 projects, it can also be used as an independent templating
language in PHP projects.

In Fluid, all dynamic output is escaped by default, which makes the templating
engine secure by default. This prevents common XSS (Cross Site Scripting)
mistakes that can easily happen in HTML templates.

Fluid comes with a range of so-called ViewHelpers that allow various formatting
and output modification directly in the template. Custom logic can be added
by providing custom ViewHelper implementations through a straightforward
PHP API.
