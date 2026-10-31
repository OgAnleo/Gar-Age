import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    FileText,
    FolderGit2,
    LayoutGrid,
    Receipt,
    Users,
    Warehouse,
} from 'lucide-react';
import ContractController from '@/actions/App/Http/Controllers/Tenant/ContractController';
import CustomerController from '@/actions/App/Http/Controllers/Tenant/CustomerController';
import FeeController from '@/actions/App/Http/Controllers/Tenant/FeeController';
import UnitController from '@/actions/App/Http/Controllers/Tenant/UnitController';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
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
    ];
}

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

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
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
