<?php

namespace Tools\QualityGate;

final readonly class CheckResult
{
    /**
     * @param  list<string>  $details  Offending items, shown when the check fails.
     */
    public function __construct(
        public string $name,
        public bool $passed,
        public string $summary,
        public array $details = [],
    ) {}
}
