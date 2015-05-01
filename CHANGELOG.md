TYPO3.Fluid Changelog
=====================

1.2.0 - Upcoming
------------------

- Added `getByPath` method to `VariableProviderInterface`. Any class currently subclassing `StandardVariableProvider` is not
  affected; any class *not* subclassing this will need to add this method or be changed to subclass `StandardVariableProvider`.
- Added support for getters and asserters when reading property values from objects; a class with a `getSpecialProperty` method
  can now be accessed like `{object.specialProperty}` in Fluid templates. Supports `is` style assserters - method `isEnabled` on
  an object can be accessed like `{object.enabled}` in Fluid templates.
- Fixed an issue with ViewHelper arguments defined to require class instances throwing an error when argument is actually given.
  Error is now thrown only if provided argument does not implement or subclass the expected class (Traits also supported).

1.1.0 - 2015-04-26
------------------

- Removed legacy namespace registration method `{namespace foo=Php\Name\Space}`
- Made only way to register namespaces from templates, using `<fluid xmlns:foo="">...</fluid>` which is autocompletion
  compatible and supports both namespace URLs and PHP namespaces. Namespaces can still be attached via ViewHelperResolver.
- Command line interface added (GCI, Socket, Direct HTTP modes included).
- VariableProvider pattern introduced to replace the fairly rigid TemplateVariableContainer with a dyanically capable pattern.
- StandardVariableProvider introduced as new basic VariableProvider, JSONVariableProvider introduced to use JSON as variables.
- TemplateProcessor pattern allowing classes to manipulate/validate template source code before parsing happens.
- Improved examples
- Introduced HHVM support
- Fixed a handful of bugs around cache

1.0.0 - 2015-04-07
------------------

- Decoupled from `TYPO3.Flow` dependency to work as standalone rendering engine.
- Changelog started
