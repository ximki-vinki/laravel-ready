<?php

use PHPUnit\Framework\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

require __DIR__.'/../vendor/autoload.php';

pest()->extend(TestCase::class)->in('Unit', 'Feature');

function projectRoot(): string
{
    return dirname(__DIR__);
}
