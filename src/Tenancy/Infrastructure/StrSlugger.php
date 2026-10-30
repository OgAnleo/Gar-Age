<?php

namespace GarAge\Tenancy\Infrastructure;

use GarAge\Tenancy\Domain\Slugger;
use Illuminate\Support\Str;

/**
 * Adapter: Slugger port implemented with Laravel's Str::slug.
 */
final class StrSlugger implements Slugger
{
    public function slugify(string $text): string
    {
        return Str::slug($text);
    }
}
