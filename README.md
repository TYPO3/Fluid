TYPO3.Fluid Rendering Engine
============================

[![Build Status](https://img.shields.io/travis/TYPO3/Fluid/master.svg?style=flat-square)](https://travis-ci.org/TYPO3/Fluid/branches)
[![Coverage](https://img.shields.io/coveralls/TYPO3/Fluid/master.svg?style=flat-square)](https://coveralls.io/r/TYPO3/Fluid?branch=master)
[![Scrutinizer](https://scrutinizer-ci.com/g/TYPO3/Fluid/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TYPO3/Fluid/)

TYPO3 community template engine - composer-enabled, Flow/CMS dependency-free PSR-4 edition.

Installation
------------

1. Include as composer dependency using `composer require typo3fluid/fluid`
2. Run `composer install` to generate the vendor class autoloader
3. The classes from `TYPO3.Fluid` can now be used in your composer project

Usage Examples
--------------

Small usage examples have been included in the [examples/](examples/) folder. The examples are PHP scripts which render the
templates and their atomic partials and layouts from the folders. In the PHP files you can find the most basic implementation
example - and you can execute the examples by running them through your HTTPD or calling `php examples/example_variables.php` etc.

> Tip: you can execute all examples in the same run by calling
>
> `find examples/ -depth 1 -name *.php -exec php {} \;`

Usage Documentation
-------------------

* [The Fluid template file structure - where to place which template files](doc/FLUID_STRUCTURE.md)
* [The Fluid syntax - how the special Fluid syntax works](doc/FLUID_SYNTAX.md)
* [ViewHelpers - what these classes do in the Fluid language](doc/FLUID_VIEWHELPERS.md)

Developer Documentation
-----------------------

* [Implementing Fluid - controlling how Fluid behaves in your application](doc/FLUID_IMPLEMENTATION.md)
* [Creating ViewHelpers - special PHP classes to create custom dynamic tags](doc/FLUID_CREATING_VIEWHELPERS.md)
* [Creating ExpressionNodes - special PHP classes that extend the Fluid syntax](doc/FLUID_EXPRESSIONS.md)
* [Special difference information for developers coming from TYPO3 Flow/CMS](doc/README_TYPO3.md)
