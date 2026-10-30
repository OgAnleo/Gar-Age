<?php

namespace Tools\QualityGate;

use RuntimeException;

/**
 * The gate formula: minimum coverage, maximum duplication and maximum CRAP.
 */
final readonly class Thresholds
{
    public function __construct(
        public float $coverageMin,
        public float $duplicationMax,
        public float $crapMax,
    ) {}

    public static function fromFile(string $path): self
    {
        $contents = is_file($path) ? file_get_contents($path) : false;
        $config = $contents === false ? null : json_decode($contents, true);

        if (! is_array($config)) {
            throw new RuntimeException("Invalid or missing thresholds file: {$path}");
        }

        return new self(
            self::number($config, 'coverage_min'),
            self::number($config, 'duplication_max'),
            self::number($config, 'crap_max'),
        );
    }

    /**
     * @param  array<mixed>  $config
     */
    private static function number(array $config, string $key): float
    {
        if (! isset($config[$key]) || ! is_numeric($config[$key])) {
            throw new RuntimeException("Threshold '{$key}' must be a number.");
        }

        return (float) $config[$key];
    }
}
