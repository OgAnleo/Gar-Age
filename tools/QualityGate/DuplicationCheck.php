<?php

namespace Tools\QualityGate;

final readonly class DuplicationCheck implements Check
{
    public function __construct(
        private JscpdReport $report,
        private float $maxPercent,
    ) {}

    public function run(): CheckResult
    {
        $duplication = $this->report->duplicatedPercentage();
        $passed = $duplication <= $this->maxPercent;

        return new CheckResult(
            'Duplication (DRY)',
            $passed,
            sprintf('%.2f%% of lines duplicated (max %s%%)', $duplication, $this->maxPercent),
            $passed ? [] : $this->report->clones(),
        );
    }
}
