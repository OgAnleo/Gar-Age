<?php

namespace GarAge\Catalog\Application;

use GarAge\Catalog\Domain\PublicPage;
use GarAge\Catalog\Domain\PublicPageRepository;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: publish or hide the business's public page and set its contact details.
 */
final readonly class UpdatePublicPage
{
    public function __construct(private PublicPageRepository $pages) {}

    /**
     * @throws InvalidValue
     */
    public function handle(TenantId $tenantId, bool $enabled, ?string $phone, ?string $email): void
    {
        $this->pages->save($tenantId, new PublicPage(
            $enabled,
            $phone === null ? null : new PhoneNumber($phone),
            $email === null ? null : new Email($email),
        ));
    }
}
