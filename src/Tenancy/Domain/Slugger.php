<?php

namespace GarAge\Tenancy\Domain;

/**
 * Port: turns free text into a URL-safe slug ("Trasteros Norte" → "trasteros-norte").
 */
interface Slugger
{
    public function slugify(string $text): string;
}
