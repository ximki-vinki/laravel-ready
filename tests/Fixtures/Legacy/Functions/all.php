<?php

define('LEGACY_MODE', true);
extract([]);
compact('a');
eval('');
utf8_encode('');
utf8_decode('');
parse_str('foo=bar');
session_start();
setcookie('name', 'value');
header('Content-Type: text/plain');
mail('a@b.c', 'subject', 'body');
strftime('%Y-%m-%d');
putenv('APP_ENV=legacy');
getenv('APP_ENV');
assert('true');
