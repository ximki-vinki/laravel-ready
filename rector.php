<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests/Unit',
        __DIR__.'/tests/Feature',
    ])
    ->withSkip([
        __DIR__.'/tests/Fixtures',
        RemoveAlwaysTrueIfConditionRector::class => [
            __DIR__.'/src/Console/Application.php',
        ],
        RemoveUnreachableStatementRector::class => [
            __DIR__.'/src/Console/Application.php',
        ],
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        earlyReturn: true,
        instanceOf: true,
    );
