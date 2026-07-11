<?php

declare(strict_types=1);

namespace LaravelReady\Analysis\Readiness\Use;

use Illuminate\Support\Collection;
use LaravelReady\Analysis\AnalysisResult;
use LaravelReady\Analysis\Findings\UseFinding;
use LaravelReady\Analysis\Findings\UseImportFinding;

abstract readonly class UsePolicy
{
    /**
     * @return list<UseRule>
     */
    abstract protected function rules(): array;

    /**
     * @return Collection<array-key, UseFinding>
     */
    public function violations(AnalysisResult $result): Collection
    {
        $violations = collect();

        foreach ($result->findings as $finding) {
            if (! $finding instanceof UseImportFinding) {
                continue;
            }

            foreach ($this->rules() as $rule) {
                if ($rule->isDenied($finding)) {
                    $violations->push(new UseFinding($finding->fqcn, $finding->line));

                    break;
                }
            }
        }

        return $violations;
    }
}
