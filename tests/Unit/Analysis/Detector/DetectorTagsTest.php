<?php

declare(strict_types=1);

use LaravelReady\Analysis\Detector;
use LaravelReady\Analysis\Enums\SuperglobalName;
use LaravelReady\Analysis\Enums\Tag;
use LaravelReady\Analysis\Findings\SuperglobalFinding;
use LaravelReady\Analysis\Findings\TagFinding;

covers(Detector::class);

it('detects tag on clean fixture', function (Tag $expected, string $path, int $line): void {
    $result = (new Detector)->analyse(fixture($path));

    expect($result->findings)->toContainEqual(new TagFinding($expected, $line));
})->with([
    'legacy-code on class' => [Tag::Legacy, 'Tags/legacy-code/class.php', 4],
    'legacy-code on function' => [Tag::Legacy, 'Tags/legacy-code/function.php', 4],
    'legacy-code on method' => [Tag::Legacy, 'Tags/legacy-code/method.php', 6],
    'laravel-ready on class' => [Tag::LaravelReady, 'Tags/laravel-ready/class.php', 4],
    'laravel-adapter on class' => [Tag::LaravelAdapter, 'Tags/laravel-adapter/class.php', 4],
    'legacy-adapter on class' => [Tag::LegacyAdapter, 'Tags/legacy-adapter/class.php', 4],
    'legacy-perfect on class' => [Tag::LegacyPerfect, 'Tags/legacy-perfect/class.php', 4],
]);

it('detects no tag in clean fixtures', function (string $fixture): void {
    $result = (new Detector)->analyse(fixture('Tags/Clean/'.$fixture));

    expect($result->findings->filter(
        fn ($finding): bool => $finding instanceof TagFinding,
    ))->toBeEmpty();
})->with([
    'empty' => ['empty.php'],
    'no-tag' => ['no-tag.php'],
    'line-comment' => ['line-comment.php'],
    'similar-tag' => ['similar-tag.php'],
]);

it('detects tag alongside blockers in mixed fixture', function (): void {
    $result = (new Detector)->analyse(fixture('Tags/Mixed/tag-and-blocker.php'));

    expect($result->findings)->toContainEqual(new TagFinding(Tag::Legacy, 4))
        ->and($result->findings)->toContainEqual(new SuperglobalFinding(SuperglobalName::Get, 8));
});
