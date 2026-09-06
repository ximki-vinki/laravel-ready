<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Allows\AllowsParseResult;
use LaravelReady\Analysis\Findings\Finding;

final class FindingSectionBuilder
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return Collection<array-key, FindingSection>
     */
    public function build(Collection $findings, ?AllowsParseResult $allows = null): Collection
    {
        $sections = collect();

        foreach (FindingSectionLabel::legacy() as $label) {
            $group = $label === FindingSectionLabel::Allows
                ? ($allows instanceof AllowsParseResult ? $allows->unknowns : collect())
                : $findings
                    ->filter(fn (Finding $finding): bool => $finding instanceof ($label->findingClass()))
                    ->values();

            if ($group->isNotEmpty()) {
                $sections->push(new FindingSection($label, $group));
            }
        }

        return $sections;
    }
}
