export type Tenant = {
    id: number;
    name: string;
    slug: string;
    /** Owners manage the business (pricing, team, settings); staff only run it. */
    canManage: boolean;
};
