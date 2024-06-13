--TEST--
echo "|<f:format.trim>  Hello world!  </f:format.trim>|" | fluid run
--ARGS--
run
--STDIN--
|<f:format.trim>  Hello world!  </f:format.trim>|
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--
|Hello world!|
