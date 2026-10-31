import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    FileText,
    LayoutGrid,
    Percent,
    Receipt,
    Users,
    Warehouse,
} from 'lucide-react';
import BusinessController from '@/actions/App/Http/Controllers/Tenant/BusinessController';
import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import CustomerController from '@/actions/App/Http/Controllers/Tenant/CustomerController';
import FeeController from '@/actions/App/Http/Controllers/Tenant/FeeController';
import PricingController from '@/actions/App/Http/Controllers/Tenant/PricingController';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { dashboard as tenantDashboard } from '@/routes/tenant';
import type { NavItem } from '@/types';

/**
 * Inside a tenant the sidebar shows its sections; outside, the generic dashboard.
 * Tenant URLs get their slug from useTenantUrlDefaults (called by AppLayout).
 */
function useMainNavItems(): NavItem[] {
    const { tenant } = usePage().props;

    if (!tenant) {
        return [{ title: 'Dashboard', href: dashboard(), icon: LayoutGrid }];
    }

    return [
        { title: 'Panel', href: tenantDashboard(), icon: LayoutGrid },
        { title: 'Unidades', href: UnitController.index(), icon: Warehouse },
        { title: 'Clientes', href: CustomerController.index(), icon: Users },
        {
            title: 'Contratos',
            href: ContractController.index(),
            icon: FileText,
        },
        { title: 'Cuotas', href: FeeController.index(), icon: Receipt },
        { title: 'Precios', href: PricingController.edit(), icon: Percent },
        { title: 'Negocio', href: BusinessController.edit(), icon: Building2 },
    ];
}

export function AppSidebar() {
    const mainNavItems = useMainNavItems();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
