<?php

define('A', '1');
$ok = define('B', '2');

function f(): mixed
{
    return define('C', '3');
}
