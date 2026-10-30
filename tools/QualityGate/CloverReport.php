<?php

namespace Tools\QualityGate;

use RuntimeException;
use SimpleXMLElement;

/**
 * Reads the Clover XML that PHPUnit/Pest writes with --coverage-clover.
 */
final readonly class CloverReport
{
    private function __construct(private SimpleXMLElement $xml) {}

    public static function fromFile(string $path): self
    {
        $xml = is_file($path) ? simplexml_load_file($path) : false;

        if ($xml === false) {
            throw new RuntimeException("Clover report not found or invalid: {$path}");
        }

        return new self($xml);
    }

    /**
     * Percentage of executable lines covered by the tests.
     */
    public function lineCoverage(): float
    {
        $metrics = $this->xml->project->metrics;
        $statements = (int) $metrics['statements'];
        $covered = (int) $metrics['coveredstatements'];

        return $statements === 0 ? 100.0 : $covered / $statements * 100;
    }

    /**
     * @return list<array{method: string, crap: float}>
     */
    public function methodCrapScores(): array
    {
        $scores = [];

        foreach ($this->xml->xpath('//file') ?: [] as $file) {
            $owner = isset($file->class['name']) ? (string) $file->class['name'] : basename((string) $file['name']);

            foreach ($file->xpath('line[@type="method"]') ?: [] as $line) {
                $scores[] = [
                    'method' => $owner.'::'.$line['name'],
                    'crap' => (float) $line['crap'],
                ];
            }
        }

        return $scores;
    }
}
