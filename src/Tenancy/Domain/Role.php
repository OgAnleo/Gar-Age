<?php

namespace GarAge\Tenancy\Domain;

/**
 * Role of a user inside a tenant.
 */
enum Role: string
{
    case Owner = 'owner';
    case Staff = 'staff';
}
