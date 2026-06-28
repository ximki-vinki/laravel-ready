<?php

declare(strict_types=1);

namespace LaravelReady\Analysis;

enum SuperglobalName: string
{
    case Globals = 'GLOBALS';
    case Server = '_SERVER';
    case Get = '_GET';
    case Post = '_POST';
    case Files = '_FILES';
    case Cookie = '_COOKIE';
    case Session = '_SESSION';
    case Request = '_REQUEST';
    case Env = '_ENV';
}
