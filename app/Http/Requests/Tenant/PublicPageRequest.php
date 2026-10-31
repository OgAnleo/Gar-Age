<?php

namespace App\Http\Requests\Tenant;

use App\Rules\DomainValue;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the public page settings, reusing the contact value objects.
 */
class PublicPageRequest extends FormRequest
{
    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'public_page_enabled' => ['required', 'boolean'],
            'contact_phone' => ['nullable', 'string', 'max:20',
                new DomainValue(fn (string $value) => new PhoneNumber($value), 'El teléfono debe tener entre 9 y 15 dígitos.')],
            'contact_email' => ['nullable', 'string', 'max:'.Email::MAX_LENGTH,
                new DomainValue(fn (string $value) => new Email($value), 'El email no es válido.')],
        ];
    }

    public function enabled(): bool
    {
        return $this->boolean('public_page_enabled');
    }

    public function phone(): ?string
    {
        return $this->filled('contact_phone') ? $this->string('contact_phone')->toString() : null;
    }

    public function email(): ?string
    {
        return $this->filled('contact_email') ? $this->string('contact_email')->toString() : null;
    }
}
