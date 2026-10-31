<?php

namespace App\Http\Requests\Tenant;

use App\Rules\DomainValue;
use GarAge\Customers\Application\CustomerInput;
use GarAge\Customers\Domain\CustomerName;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Customers\Domain\TaxId;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Input adapter for creating and updating customers: checks the shape of the
 * form data, delegating format rules to the domain value objects. Business
 * rules (unique tax id per tenant) live in the domain.
 */
class CustomerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:'.CustomerName::MAX_LENGTH],
            'tax_id' => ['required', 'string', 'max:20',
                new DomainValue(fn (string $value) => new TaxId($value), 'El DNI, NIE o CIF no es válido.')],
            'email' => ['nullable', 'string', 'max:'.Email::MAX_LENGTH,
                new DomainValue(fn (string $value) => new Email($value), 'El email no es válido.')],
            'phone' => ['nullable', 'string', 'max:20',
                new DomainValue(fn (string $value) => new PhoneNumber($value), 'El teléfono debe tener entre 9 y 15 dígitos.')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',
            'tax_id.required' => 'El DNI, NIE o CIF es obligatorio.',
            'tax_id.max' => 'El DNI, NIE o CIF no puede tener más de :max caracteres.',
            'email.max' => 'El email no puede tener más de :max caracteres.',
            'phone.max' => 'El teléfono no puede tener más de :max caracteres.',
        ];
    }

    public function customerInput(): CustomerInput
    {
        return new CustomerInput(
            name: $this->string('name')->toString(),
            taxId: $this->string('tax_id')->toString(),
            email: $this->optionalString('email'),
            phone: $this->optionalString('phone'),
        );
    }

    /**
     * Empty optional fields become null (the domain then stores nothing).
     */
    private function optionalString(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->toString() : null;
    }
}
