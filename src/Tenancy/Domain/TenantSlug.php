<?php

namespace GarAge\Tenancy\Domain;

use GarAge\Shared\Domain\InvalidValue;

/**
 * URL identifier of a tenant: /{slug}/dashboard.
 */
final readonly class TenantSlug
{
    /**
     * Slugs that collide with application routes.
     */
    private const array RESERVED = [
        'admin', 'api', 'businesses', 'cron', 'dashboard', 'email', 'forgot-password', 'login', 'logout',
        'passkeys', 'register', 'reset-password', 'settings', 'storage',
        'two-factor-challenge', 'up', 'user',
    ];

    public function __construct(public string $value)
    {
        if (preg_match('/^[\p{Ll}\p{N}]+(?:-[\p{Ll}\p{N}]+)*$/u', $value) !== 1 || mb_strlen($value) > 255) {
            throw new InvalidValue(sprintf('"%s" is not a valid tenant slug.', $value));
        }
    }

    /**
     * Build from an already slugified name, falling back when it is empty.
     */
    public static function fromSlugified(string $slugified): self
    {
        return new self($slugified === '' ? 'tenant' : $slugified);
    }

    public function withSuffix(int $suffix): self
    {
        return new self("{$this->value}-{$suffix}");
    }

    public function isReserved(): bool
    {
        return in_array($this->value, self::RESERVED, true);
    }
}
