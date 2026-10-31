import { usePage } from '@inertiajs/react';
import { setUrlDefaults } from '@/wayfinder';

/**
 * Make the current tenant slug the default {tenant} parameter of every
 * Wayfinder route, like URL::defaults() does on the server.
 *
 * It runs during render on purpose: the layout renders before the page in the
 * same pass, so the page already builds its URLs with the right slug. Inertia's
 * navigate event fires after the new page is rendered, which would be too late.
 */
export function useTenantUrlDefaults(): void {
    const { tenant } = usePage().props;

    setUrlDefaults(tenant ? { tenant: tenant.slug } : {});
}
