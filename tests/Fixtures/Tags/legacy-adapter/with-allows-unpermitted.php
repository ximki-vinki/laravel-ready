<?php

/**
 * @legacy-adapter
 * @allows $_COOKIE, setcookie
 */
class CookieAdapter
{
    public function run(): void
    {
        $_GET['id'];
    }
}
