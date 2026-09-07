<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Allows\AllowsParseResult;
use LaravelReady\Analysis\Displayable;
use LaravelReady\Analysis\Findings\Finding;

final class FindingSectionBuilder
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return Collection<array-key, FindingSection>
     */
    public function build(
        Collection $findings,
        ?AllowsParseResult $allows = null,
        ?SkipCheckNote $skipCheck = null,
    ): Collection {
        $sections = collect();

        foreach (FindingSectionLabel::legacy() as $label) {
            $group = $this->group($label, $findings, $allows, $skipCheck);

            if ($group->isNotEmpty()) {
                $sections->push(new FindingSection($label, $group));
            }
        }

        return $sections;
    }

    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return Collection<array-key, Displayable>
     */
    private function group(
        FindingSectionLabel $label,
        Collection $findings,
        ?AllowsParseResult $allows,
        ?SkipCheckNote $skipCheck,
    ): Collection {
        return match ($label) {
            FindingSectionLabel::Allows => $allows instanceof AllowsParseResult
                ? $allows->unknowns
                : collect(),
            FindingSectionLabel::Skip => $skipCheck instanceof SkipCheckNote
                ? collect([$skipCheck])
                : collect(),
            default => $findings
                ->filter(fn (Finding $finding): bool => $finding instanceof ($label->findingClass()))
                ->values(),
        };
    }
}
