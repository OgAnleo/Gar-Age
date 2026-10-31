<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\StaffMemberRequest;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\AddStaffMember;
use GarAge\Tenancy\Application\ListMembers;
use GarAge\Tenancy\Application\RemoveStaffMember;
use GarAge\Tenancy\Domain\Member;
use GarAge\Tenancy\Domain\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: the owner manages the people of the business.
 */
class TeamController extends TenantController
{
    public function index(ListMembers $listMembers): Response
    {
        return Inertia::render('tenant/team/index', [
            'members' => array_map(fn (Member $member): array => [
                'id' => $member->userId->value,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role->value,
                'role_label' => $member->role === Role::Owner ? 'Propietario' : 'Empleado',
            ], $listMembers->handle($this->tenantId())),
        ]);
    }

    /**
     * Add an employee; a new account's temporary password is shown once.
     */
    public function store(StaffMemberRequest $request, AddStaffMember $addStaffMember): RedirectResponse
    {
        $temporaryPassword = $addStaffMember->handle(
            $this->tenantId(),
            new UserId($request->user()->id),
            $request->string('name')->toString(),
            $request->string('email')->toString(),
        );

        if ($temporaryPassword !== null) {
            Inertia::flash('temporaryPassword', [
                'email' => mb_strtolower(trim($request->string('email')->toString())),
                'password' => $temporaryPassword,
            ]);
        }

        return $this->redirectWithToast('tenant.team.index', 'Empleado añadido.');
    }

    public function destroy(Request $request, int $member, RemoveStaffMember $removeStaffMember): RedirectResponse
    {
        $removeStaffMember->handle($this->tenantId(), new UserId($request->user()->id), new UserId($member));

        return $this->redirectWithToast('tenant.team.index', 'Empleado quitado del negocio.');
    }
}
