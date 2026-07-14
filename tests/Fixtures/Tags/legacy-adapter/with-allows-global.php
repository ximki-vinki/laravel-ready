<?php

/**
 * @legacy-adapter
 * @allows $_COOKIE, setcookie, global
 */
class GlobalAdapter
{
    public function read(): mixed
    {
        global $legacyStore;

        return $legacyStore;
    }
}
