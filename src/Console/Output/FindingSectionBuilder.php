<?php

declare(strict_types=1);

namespace LaravelReady\Console\Output;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\Findings\Finding;

final class FindingSectionBuilder
{
    /**
     * @param  Collection<array-key, Finding>  $findings
     * @return Collection<array-key, FindingSection>
     */
    public function build(Collection $findings): Collection
    {
        $sections = collect();

        foreach (FindingSectionLabel::legacy() as $label) {
            $class = $label->findingClass();

            $group = $findings
                ->filter(fn (Finding $finding): bool => $finding instanceof $class)
                ->values();

            if ($group->isNotEmpty()) {
                $sections->push(new FindingSection($label, $group));
            }
        }

        return $sections;
    }
}
