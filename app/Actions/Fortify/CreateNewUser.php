<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\RegisterBusiness;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private RegisterBusiness $registerBusiness) {}

    /**
     * Validate and create a newly registered user together with their tenant.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'business_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            if (! config('auth.verify_email')) {
                $user->markEmailAsVerified(); // so no verification mail is sent
            }

            $this->registerBusiness->handle($input['business_name'], new UserId($user->id));

            return $user;
        });
    }
}
