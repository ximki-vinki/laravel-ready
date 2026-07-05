<?php

declare(strict_types=1);

use LaravelReady\Analysis\Tag;
use LaravelReady\Analysis\TagFinding;
use LaravelReady\Console\Output\TagStatus;

it('resolves untagged status from empty findings', function () {
    $status = TagStatus::fromFindings(collect());

    expect($status)->toBe(TagStatus::Untagged)
        ->and($status->display())->toBe('untagged');
});

it('resolves single tag status', function () {
    $status = TagStatus::fromFindings(collect([
        new TagFinding(Tag::LaravelReady, 3),
    ]));

    expect($status)->toBe(TagStatus::LaravelReady)
        ->and($status->display())->toBe('@laravel-ready');
});

it('resolves multi tag status with unique tags', function () {
    $status = TagStatus::fromFindings(collect([
        new TagFinding(Tag::LaravelReady, 3),
        new TagFinding(Tag::Legacy, 10),
        new TagFinding(Tag::LaravelReady, 20),
    ]));

    expect($status)->toBe(TagStatus::Multi)
        ->and($status->display())->toBe('multi');
});

it('resolves laravel adapter tag status', function () {
    $status = TagStatus::fromFindings(collect([
        new TagFinding(Tag::LaravelAdapter, 4),
    ]));

    expect($status)->toBe(TagStatus::LaravelAdapter)
        ->and($status->display())->toBe('@laravel-adapter');
});

it('resolves legacy tag status', function () {
    $status = TagStatus::fromFindings(collect([
        new TagFinding(Tag::Legacy, 4),
    ]));

    expect($status)->toBe(TagStatus::Legacy)
        ->and($status->display())->toBe('@legacy-code');
});
