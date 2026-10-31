import type { Auth } from '@/types/auth';
import type { Demo } from '@/types/demo';
import type { Tenant } from '@/types/tenant';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            tenant: Tenant | null;
            demo: Demo | null;
            [key: string]: unknown;
        };
    }
}
