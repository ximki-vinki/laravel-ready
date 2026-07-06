<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Enums;

enum BlockedFunction: string
{
    case Define = 'define';
    case Extract = 'extract';
    case Compact = 'compact';
    case Eval = 'eval';
    case Utf8Encode = 'utf8_encode';
    case Utf8Decode = 'utf8_decode';
    case ParseStr = 'parse_str';
    case SessionStart = 'session_start';
    case Setcookie = 'setcookie';
    case Header = 'header';
    case Mail = 'mail';
    case Strftime = 'strftime';
    case Putenv = 'putenv';
    case Getenv = 'getenv';
    case Assert = 'assert';
}
