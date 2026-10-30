<?php

/*
| Quality gate: tests with coverage, duplication (DRY) and CRAP index.
| Thresholds live in quality-gate.json, duplication settings in .jscpd.json.
| Run it with `composer quality`; .githooks/pre-commit runs it before every commit.
*/

use Tools\QualityGate\CloverReport;
use Tools\QualityGate\ConsoleReporter;
use Tools\QualityGate\CoverageCheck;
use Tools\QualityGate\CrapCheck;
use Tools\QualityGate\DuplicationCheck;
use Tools\QualityGate\JscpdReport;
use Tools\QualityGate\QualityGate;
use Tools\QualityGate\Thresholds;

require __DIR__.'/../vendor/autoload.php';

chdir(dirname(__DIR__));

const REPORT_DIR = 'build/quality';

if (! extension_loaded('xdebug') && ! extension_loaded('pcov')) {
    fwrite(STDERR, 'No coverage driver: enable Xdebug or PCOV in the php.ini shown by `php --ini`.'.PHP_EOL);
    exit(1);
}

putenv('XDEBUG_MODE=coverage');

$thresholds = Thresholds::fromFile('quality-gate.json');

runStep('Tests with coverage', escapeshellarg(PHP_BINARY).' vendor/bin/pest --coverage-clover '.REPORT_DIR.'/clover.xml');
runStep('Duplication scan', 'node node_modules/jscpd/run-jscpd.js --config .jscpd.json');

$clover = CloverReport::fromFile(REPORT_DIR.'/clover.xml');

$results = (new QualityGate([
    new CoverageCheck($clover, $thresholds->coverageMin),
    new DuplicationCheck(JscpdReport::fromFile(REPORT_DIR.'/jscpd-report.json'), $thresholds->duplicationMax),
    new CrapCheck($clover, $thresholds->crapMax),
]))->run();

exit((new ConsoleReporter)->report($results) ? 0 : 1);

function runStep(string $label, string $command): void
{
    echo "> {$label}".PHP_EOL;
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "{$label} failed (exit {$exitCode}).".PHP_EOL);
        exit(1);
    }
}
