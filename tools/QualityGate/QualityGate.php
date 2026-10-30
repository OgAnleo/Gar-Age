<?php

namespace Tools\QualityGate;

final readonly class QualityGate
{
    /**
     * @param  list<Check>  $checks
     */
    public function __construct(private array $checks) {}

    /**
     * @return list<CheckResult>
     */
    public function run(): array
    {
        return array_map(fn (Check $check): CheckResult => $check->run(), $this->checks);
    }
}
