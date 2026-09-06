<?php

/**
 * @legacy-adapter
 * @allows $_COOKIE, not-a-thing
 */
class CookieAdapter
{
    public function run(): void
    {
        $_GET['id'];
    }
}
