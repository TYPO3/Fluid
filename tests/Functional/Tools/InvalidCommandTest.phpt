--TEST--
fluid invalid
--ARGS--
invalid
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--

ERROR! Unsupported command: invalid
