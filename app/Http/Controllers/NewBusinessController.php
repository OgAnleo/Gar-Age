<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewBusinessRequest;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\RegisterBusiness;
use Illuminate\Http\RedirectResponse;

/**
 * HTTP adapter: a user without a business (e.g. after deleting theirs) creates one.
 */
class NewBusinessController extends Controller
{
    public function __invoke(NewBusinessRequest $request, RegisterBusiness $registerBusiness): RedirectResponse
    {
        $tenant = $registerBusiness->handle($request->string('business_name')->toString(), new UserId($request->user()->id));

        return to_route('tenant.dashboard', ['tenant' => $tenant->slug->value]);
    }
}
