<?php

use Tools\QualityGate\CheckResult;
use Tools\QualityGate\CloverReport;
use Tools\QualityGate\ConsoleReporter;
use Tools\QualityGate\CoverageCheck;
use Tools\QualityGate\CrapCheck;
use Tools\QualityGate\DuplicationCheck;
use Tools\QualityGate\JscpdReport;
use Tools\QualityGate\QualityGate;
use Tools\QualityGate\Thresholds;

function qualityFixture(string $name): string
{
    return __DIR__.'/../Fixtures/quality/'.$name;
}

it('passes coverage when the line percentage reaches the minimum', function () {
    $clover = CloverReport::fromFile(qualityFixture('clover.xml'));

    expect((new CoverageCheck($clover, 70))->run()->passed)->toBeTrue()
        ->and((new CoverageCheck($clover, 80))->run())
        ->passed->toBeFalse()
        ->summary->toContain('75.0%');
});

it('reports only the methods above the CRAP limit', function () {
    $result = (new CrapCheck(CloverReport::fromFile(qualityFixture('clover.xml')), 30))->run();

    expect($result->passed)->toBeFalse()
        ->and($result->details)->toBe(['App\Messy::tangled  CRAP 72.0'])
        ->and($result->summary)->toContain('highest 72.0');
});

it('fails duplication above the limit and lists the clones', function () {
    $report = JscpdReport::fromFile(qualityFixture('jscpd-report.json'));

    expect((new DuplicationCheck($report, 3))->run()->passed)->toBeTrue()
        ->and((new DuplicationCheck($report, 1))->run())
        ->passed->toBeFalse()
        ->details->toBe(['a.php:1-10 <-> b.php:5-14']);
});

it('reads the thresholds and rejects a missing value', function () {
    $path = tempnam(sys_get_temp_dir(), 'gate');

    file_put_contents($path, '{"coverage_min": 70, "duplication_max": 1, "crap_max": 30}');
    $thresholds = Thresholds::fromFile($path);

    file_put_contents($path, '{"coverage_min": 70}');

    expect([$thresholds->coverageMin, $thresholds->duplicationMax, $thresholds->crapMax])->toBe([70.0, 1.0, 30.0])
        ->and(fn () => Thresholds::fromFile($path))->toThrow(RuntimeException::class, 'duplication_max');

    unlink($path);
});

it('runs every check and reports failure when one fails', function () {
    $clover = CloverReport::fromFile(qualityFixture('clover.xml'));
    $results = (new QualityGate([
        new CoverageCheck($clover, 70),
        new CrapCheck($clover, 30),
    ]))->run();

    ob_start();
    $passed = (new ConsoleReporter)->report($results);
    $output = (string) ob_get_clean();

    expect($results)->toHaveCount(2)
        ->and($passed)->toBeFalse()
        ->and($output)->toContain('[FAIL] CRAP index')
        ->and($output)->toContain('Quality gate failed.');
});

it('reports success when every check passes', function () {
    ob_start();
    $passed = (new ConsoleReporter)->report([new CheckResult('Coverage', true, 'ok')]);
    $output = (string) ob_get_clean();

    expect($passed)->toBeTrue()
        ->and($output)->toContain('All checks passed.');
});
