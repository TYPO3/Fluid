TYPO3.Fluid Changelog
=====================

1.1.0 - Upcoming
----------------

- Removed legacy namespace registration method `{namespace foo=Php\Name\Space}`
- Made only way to register namespaces from templates, using `<f:fluid xmlns:foo="">...</f:fluid>` which is autocompletion
  compatible and supports both namespace URLs and PHP namespaces. Namespaces can still be attached via ViewHelperResolver.

1.0.0 - 2015-04-07
------------------

- Decoupled from `TYPO3.Flow` dependency to work as standalone rendering engine.
- Changelog started
