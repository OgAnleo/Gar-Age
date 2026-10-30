<?php

namespace Tools\QualityGate;

use RuntimeException;

/**
 * Reads the JSON report that jscpd writes with the json reporter.
 */
final readonly class JscpdReport
{
    /**
     * @param  array<string, mixed>  $data
     */
    private function __construct(private array $data) {}

    public static function fromFile(string $path): self
    {
        $contents = is_file($path) ? file_get_contents($path) : false;
        $data = $contents === false ? null : json_decode($contents, true);

        if (! is_array($data)) {
            throw new RuntimeException("jscpd report not found or invalid: {$path}");
        }

        return new self($data);
    }

    /**
     * Percentage of scanned lines that are duplicated.
     */
    public function duplicatedPercentage(): float
    {
        $statistics = $this->data['statistics'] ?? null;
        $percentage = is_array($statistics) && is_array($statistics['total'] ?? null)
            ? ($statistics['total']['percentage'] ?? 0)
            : 0;

        return is_numeric($percentage) ? (float) $percentage : 0.0;
    }

    /**
     * @return list<string> Each clone as "fileA:start-end <-> fileB:start-end".
     */
    public function clones(): array
    {
        $duplicates = $this->data['duplicates'] ?? [];

        if (! is_array($duplicates)) {
            return [];
        }

        return array_values(array_map(
            fn (mixed $clone): string => is_array($clone)
                ? self::location($clone['firstFile'] ?? null).' <-> '.self::location($clone['secondFile'] ?? null)
                : '?',
            $duplicates,
        ));
    }

    private static function location(mixed $file): string
    {
        if (! is_array($file)) {
            return '?';
        }

        return sprintf('%s:%s-%s', $file['name'] ?? '?', $file['start'] ?? '?', $file['end'] ?? '?');
    }
}
