--TEST--
echo "{_test}" | fluid analyze --stdin
--ARGS--
analyze --stdin
--STDIN--
{_test}
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--
[ERROR] Fluid parse error in template php://stdin, line 2 at character 1. Error: Variable identifiers cannot start with a "_": _test (error code 1765900762). Template source chunk: {_test}
