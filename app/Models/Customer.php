<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\CustomerFactory;
use GarAge\Customers\Domain\Customer as DomainCustomer;
use GarAge\Customers\Domain\CustomerDetails;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerName;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Customers\Domain\TaxId;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $tax_id
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'tax_id', 'email', 'phone'])]
class Customer extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * Map this row to the Customers domain entity.
     */
    public function toDomain(): DomainCustomer
    {
        return DomainCustomer::restore(
            new CustomerId($this->id),
            new TenantId($this->tenant_id),
            new CustomerDetails(
                new CustomerName($this->name),
                new TaxId($this->tax_id),
                $this->email === null ? null : new Email($this->email),
                $this->phone === null ? null : new PhoneNumber($this->phone),
            ),
        );
    }
}
