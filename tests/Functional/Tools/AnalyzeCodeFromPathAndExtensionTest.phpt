--TEST--
fluid analyze --path tests/Functional/Fixtures/Validation/Cli/ --extension txt
--ARGS--
analyze --path tests/Functional/Fixtures/Validation/Cli/ --extension txt
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--
[DEPRECATION] tests/Functional/Fixtures/Validation/Cli/DeprecatedViewHelper.txt: ViewHelper is deprecated.
