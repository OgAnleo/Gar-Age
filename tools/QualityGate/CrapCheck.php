<?php

namespace Tools\QualityGate;

/**
 * CRAP = complexity² × (1 − coverage)³ + complexity, per method.
 * High values mean complex code with too few tests.
 */
final readonly class CrapCheck implements Check
{
    public function __construct(
        private CloverReport $report,
        private float $max,
    ) {}

    public function run(): CheckResult
    {
        $scores = $this->report->methodCrapScores();
        usort($scores, fn (array $a, array $b): int => $b['crap'] <=> $a['crap']);

        $offenders = array_filter($scores, fn (array $score): bool => $score['crap'] > $this->max);
        $highest = $scores[0]['crap'] ?? 0.0;

        return new CheckResult(
            'CRAP index',
            $offenders === [],
            sprintf('highest %.1f (max %s)', $highest, $this->max),
            array_values(array_map(
                fn (array $score): string => sprintf('%s  CRAP %.1f', $score['method'], $score['crap']),
                $offenders,
            )),
        );
    }
}
