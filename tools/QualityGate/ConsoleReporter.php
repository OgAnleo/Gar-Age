<?php

namespace Tools\QualityGate;

final class ConsoleReporter
{
    /**
     * Print the results and tell whether every check passed.
     *
     * @param  list<CheckResult>  $results
     */
    public function report(array $results): bool
    {
        echo PHP_EOL.'Quality gate'.PHP_EOL;

        foreach ($results as $result) {
            printf('  [%s] %s: %s%s', $result->passed ? ' OK ' : 'FAIL', $result->name, $result->summary, PHP_EOL);

            foreach ($result->details as $detail) {
                echo "         - {$detail}".PHP_EOL;
            }
        }

        $passed = array_filter($results, fn (CheckResult $result): bool => ! $result->passed) === [];

        echo ($passed ? 'All checks passed.' : 'Quality gate failed.').PHP_EOL;

        return $passed;
    }
}
