--TEST--
fluid analyze --path tests/Functional/Fixtures/Validation/Cli/
--ARGS--
analyze --path tests/Functional/Fixtures/Validation/Cli/
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--
[ERROR] tests/Functional/Fixtures/Validation/Cli/AccessToInvalidVariableName.fluid.html: Fluid parse error in template tests/Functional/Fixtures/Validation/Cli/AccessToInvalidVariableName.fluid.html, line 2 at character 1. Error: Variable identifiers cannot start with a "_": _something.sub (error code 1765900762). Template source chunk: {_something.sub}


[DEPRECATION] tests/Functional/Fixtures/Validation/Cli/DeprecatedViewHelper.fluid.html: ViewHelper is deprecated.
