What Are ViewHelpers?
=====================

ViewHelpers are special classes which build on base classes provided by Fluid. These classes can then be imported and used as
part of the Fluid language. Your own, or some third-party package, may provide ViewHelper classes - some may even require you to
use their versions of ViewHelpers. Contrary to the built-in ViewHelpers, such third-party ViewHelpers must be imported. In Fluid,
a collection of ViewHelpers is always identified by a short name that matches a longer PHP namespace that is used as prefix for
classes when resolving which PHP class corresponds to a certain ViewHelper.

Registering/importing ViewHelpers
---------------------------------

When you need to use third-party ViewHelpers in your templates there are two equally valid options. The first of which makes your
registered namespace available in _all template files_ without further importing:

```php
$view = new TemplateView();
$view->getTemplatePaths()->registerNamespace('foo', 'Vendor\\Foo\\ViewHelpers');
```

Beware though: once imported globally, an imported namespace can no longer be overridden by `{namespace}` in template files.

And the latter method which can be used in each template file that requires the ViewHelpers:

```xml
<f:fluid xmlns:foo="Vendor\Foo\ViewHelpers">
<f:layout name="Default" />
<f:section name="Main">
    <!-- ... --->
</f:section>
</f:fluid>
```

Or using the alternative `xmlns` approach:

```xml
<f:fluid xmlns:foo="http://typo3.org/ns/Vendor/Foo/ViewHelpers">
<f:layout name="Default" />
    <f:section name="Main">
        <!-- ... --->
    </f:section>
</f:fluid>
```

Once you have registered/imported the ViewHelper collection (we call it a collection here even if it contains only one class) you
can start using it in your templates via the namespace alias you used when registering (in this example: `foo` is the alias name).

Using ViewHelpers in templates
------------------------------

ViewHelpers work by accepting either one or both of tag content (which can be HTML or other variables) and arguments which are
defined as tag attributes. How you write ViewHelper syntax is documented in the [chapter about syntax](FLUID_SYNTAX.md) - with a
few examples.

Which arguments a particular VieWHelper supports and which ViewHelpers are available is determined by the packages you have
installed. If you only have Fluid installed, there are only the ViewHelpers in [src/ViewHelpers](../src/ViewHelpers/) which you
can use. See also the documentation of any third-party packages you use; such documentation should also describe ViewHelpers.

To know which arguments a ViewHelper supports and what does arguments do, the most basic and always available way is to inspect
the class that corresponds to a ViewHelper. Such classes are usually placed in the `Vendor\Package\ViewHelpers` PHP namespace
(where `Vendor` and `Package` are obviously placeholders for actual values) and follow the following naming convention:

* `v:format.raw` becomes PHP class `TYPO3\Fluid\ViewHelpers\Format\RawViewHelper`
* `v:render` becomes PHP class `TYPO3\Fluid\ViewHelpers\RenderViewHelper`
* `mypkg:custom.specialFormat` becomes PHP class `My\Package\ViewHelpers\Custom\SpecialFormatViewHelper` assuming you added
  `xmlns:mpkg="My\Package\ViewHelpers"` or alternative namespace registration (see above).

And so on.

The arguments a ViewHelper supports will be verbosely registered in the `initializeArguments` function of each ViewHelper class.
Inspect this method to see the names, types, descriptions, required flag and default value of all attributes. An example argument
definition looks like this:

```php
public function initializeArguments() {
    $this->registerArgument('myArgument', 'boolean', 'If TRUE, makes ViewHelper do foobar', FALSE, FALSE);
}
```

Which translated to human terms means that we:

* Register an argument named `myArgument`
* Specify that it must be a boolean value or an expression resulting in a boolean value (you can find a few examples of such
  expressions in the [conditions example](../examples/Singles/Conditions.html)). Other valid types are `integer`, `string`,
  `float`, `array`, `DateTime` and other class names.
* Describe the argument's behavior in simple terms.
* Specify that the argument is not required (the 4th argument is `FALSE`).
* Specify that if the argument is not written when calling the ViewHelper, a default value of `FALSE` is assumed (5th argument).

The ViewHelper itself would then - assuming the class was named as our example above - be callable using:

```xml
<mypkg:custom.specialFormat myArgument="TRUE">{somevariable}</mypkg:custom.specialFormat>
```

What the argument does is then decided by the ViewHelper.

ViewHelper docs and schema for IDE autocompletion
-------------------------------------------------

The original and framework-coupled version of Fluid had a few assistance utilities which allowed generating a special XSD schema
which was useful both for an IDE to allow autocompletion (by leveraging standard XML schema functions) and for online
documentation rendering.

Unfortunately, at the time of writing this (April 2015) these tools have still not been decoupled and can therefore not at this
time generate the required schema files for this package and any third party package which provides ViewHelpers for this package.

Porting will of course be done and the necessary documentation placed online - but until it is complete, your only option is to
inspect the PHP class files as documented above.
