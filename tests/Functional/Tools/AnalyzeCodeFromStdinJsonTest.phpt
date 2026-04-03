--TEST--
echo "{_test}" | fluid analyze --stdin --json
--ARGS--
analyze --stdin --json
--STDIN--
{_test}
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECTF--
{"identifier":"template__5adb1a7702b9dcbf","path":"php:\/\/stdin","errors":[{"file":"%s\/Core\/Parser\/TemplateParser.php","line":130,"message":"Fluid parse error in template php:\/\/stdin, line 2 at character 1. Error: Variable identifiers cannot start with a \"_\": _test (error code 1765900762). Template source chunk: {_test}\n","templateLocation":{"identifierOrPath":"php:\/\/stdin","line":2,"character":1}}],"deprecations":[]}
