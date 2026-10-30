<?php

namespace Tools\QualityGate;

final readonly class CoverageCheck implements Check
{
    public function __construct(
        private CloverReport $report,
        private float $minPercent,
    ) {}

    public function run(): CheckResult
    {
        $coverage = $this->report->lineCoverage();

        return new CheckResult(
            'Coverage',
            $coverage >= $this->minPercent,
            sprintf('%.1f%% of lines covered (min %s%%)', $coverage, $this->minPercent),
        );
    }
}
