--TEST--
echo "|{foo}|" | fluid run --variables '{"foo": "bar"}'
--STDIN--
|{foo}|
--FILE--
<?php declare(strict_types=1);
// JSON via stdin doesn't seem to work here
$argv[] = 'run';
$argv[] = '--variables';
$argv[] = '{"foo": "bar"}';
require_once __DIR__ . '/../../../bin/fluid';
--EXPECT--
|bar|
