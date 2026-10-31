<?php

namespace App\Http\Requests\Settings;

use App\Concerns\PasswordValidationRules;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\ListOwnedBusinesses;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfileDeleteRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => $this->currentPasswordRules(),
        ];
    }

    /**
     * An owner must delete their businesses first, so none is left without an owner.
     *
     * @return list<callable(Validator): void>
     */
    public function after(ListOwnedBusinesses $listOwnedBusinesses): array
    {
        return [
            function (Validator $validator) use ($listOwnedBusinesses): void {
                if ($listOwnedBusinesses->handle(new UserId($this->user()->id)) !== []) {
                    $validator->errors()->add('password', 'Antes de eliminar tu cuenta, elimina los negocios de los que eres propietario.');
                }
            },
        ];
    }
}
