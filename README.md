TYPO3.Fluid Rendering Engine
============================

[![Build Status](https://img.shields.io/travis/TYPO3Fluid/Fluid.svg?style=flat-square)](https://travis-ci.org/TYPO3Fluid/Fluid)
[![Coverage](https://img.shields.io/coveralls/TYPO3Fluid/Fluid.svg?style=flat-square)](https://coveralls.io/r/TYPO3Fluid/Fluid)

Fluid is a modern templating engine based on a xml based syntax. It helps you create clean and functional
templates while maintaining performance. Templates can be compiled into static php cache files, that
are amazingly fast. It is based on PSR-4, requires no dependencies, is easy to integrate and can be
extended in various places.

Installation
------------

```
composer require typo3fluid/fluid
```

Basic Setup
-----------

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$view = new \TYPO3Fluid\Fluid\View\TemplateView();

$view->assign('foobar', 'Hello World');
$view->assign('items', array(
	'foo',
	'bar',
	'baz'
));

$view->getTemplatePaths()->setTemplatePathAndFilename('HelloWorld.html');
echo $view->render();
```

**HelloWorld.html**

```html
<h3>{foobar}</h3>
<ul>
<f:for each="{items}" as="item">
	{item}
</f:for>
</ul>
```

Basic Syntax
------------

Fluid has two main syntax formats - the tag mode and the inline/shorthand mode.

```html
<f:count>{items}</f:count>
```

Is the same as:

```html
{items -> f:count()}
```

The reason for this is, so that you can cleanly nest viewHelpers like this:

```html
<div class="contains-{items -> f:count()}">...</f:if>
```

For more details check out the Syntax Documentation:
[The Fluid syntax - how the special Fluid syntax works](doc/FLUID_SYNTAX.md)

ViewHelpers
-----------

ViewHelpers are fluids concept of enabling logic inside by mapping xml tags to viewHelper classes.

```html
<my:helloWorld name="John"/>
```

In order to use ViewHelpers you need to register a namespace for it, in this case this could look like:

```php
$view->getViewHelperResolver()->registerNamespace('my', 'Foo\My\ViewHelpers');
```

Then it outputs ```"Hey John, how are you?"``` because fluid can parse this and map it's arguments to the
ViewHelper class like this:

```php
<?php
class HelloWorldViewHelper extends AbstractViewHelper {
	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('name', 'string', 'Tell me your name', TRUE /* make argument required */);
	}

	/**
	 * Outputs a nice greeting based on a name
	 *
	 * @return string
	 */
	public function render() {
		return sprintf('Hey %s, how are you?', $this->arguments['name']);
	}
}
```

You can define any number of arguments, required, optional, type restricted, etc. To find out more check:
[ViewHelpers - what these classes do in the Fluid language](doc/FLUID_VIEWHELPERS.md)

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
