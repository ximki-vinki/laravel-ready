<?php

/**
 * @legacy-adapter
 * @allows $_COOKIE
 */
class FirstAllowsWins
{
    /**
     * @allows setcookie, not-from-second
     */
    public function write(): void
    {
        setcookie('x', '1');
    }
}
