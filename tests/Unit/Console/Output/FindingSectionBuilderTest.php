<?php

declare(strict_types=1);

use LaravelReady\Analysis\Enums\BlockedFunction;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\FunctionCallFinding;
use LaravelReady\Analysis\Findings\GlobalFinding;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\TagFinding;
use LaravelReady\Analysis\Findings\UnknownAllowTokenFinding;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Console\Output\FindingSectionBuilder;
use LaravelReady\Console\Output\FindingSectionLabel;

it('returns no sections for empty findings', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect());

    expect($sections)->toBeEmpty();
});

it('groups superglobals under var section', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new SuperglobalFinding(SuperglobalName::Get, 3),
        new SuperglobalFinding(SuperglobalName::Cookie, 7),
    ]));

    expect($sections)->toHaveCount(1)
        ->and($sections->first()->label)->toBe(FindingSectionLabel::Var)
        ->and($sections->first()->findings)->toHaveCount(2);
});

it('groups globals and functions into separate sections', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new GlobalFinding('foo', 2),
        new FunctionCallFinding(BlockedFunction::Define, 5),
    ]));

    expect($sections)->toHaveCount(2)
        ->and($sections->get(0)->label)->toBe(FindingSectionLabel::Global)
        ->and($sections->get(1)->label)->toBe(FindingSectionLabel::Func);
});

it('orders sections as var global func use allows', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new UnknownAllowTokenFinding('not-a-thing', 4),
        new UseFinding('Wf\Legacy\OldRepo', 11),
        new FunctionCallFinding(BlockedFunction::Define, 9),
        new GlobalFinding('foo', 6),
        new SuperglobalFinding(SuperglobalName::Get, 3),
    ]));

    expect($sections->map(fn ($section) => $section->label)->all())->toBe([
        FindingSectionLabel::Var,
        FindingSectionLabel::Global,
        FindingSectionLabel::Func,
        FindingSectionLabel::Use,
        FindingSectionLabel::Allows,
    ]);
});

it('groups use findings under use section', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new UseFinding('Wf\Legacy\OldRepo', 5),
    ]));

    expect($sections)->toHaveCount(1)
        ->and($sections->first()->label)->toBe(FindingSectionLabel::Use)
        ->and($sections->first()->findings)->toHaveCount(1);
});

it('groups unknown allow tokens under allows section', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new UnknownAllowTokenFinding('not-a-thing', 5),
    ]));

    expect($sections)->toHaveCount(1)
        ->and($sections->first()->label)->toBe(FindingSectionLabel::Allows)
        ->and($sections->first()->findings)->toHaveCount(1);
});

it('excludes tag findings from sections', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new TagFinding(Tag::LaravelReady, 1),
        new SuperglobalFinding(SuperglobalName::Get, 3),
    ]));

    expect($sections)->toHaveCount(1)
        ->and($sections->first()->label)->toBe(FindingSectionLabel::Var)
        ->and($sections->first()->findings)->toHaveCount(1);
});

it('omits empty section types', function (): void {
    $sections = (new FindingSectionBuilder)->build(collect([
        new SuperglobalFinding(SuperglobalName::Get, 3),
    ]));

    expect($sections)->toHaveCount(1)
        ->and($sections->first()->label)->toBe(FindingSectionLabel::Var);
});
