/**
 * Published credentials of the public demo (null everywhere else).
 */
export type Demo = {
    email: string;
    staffEmail: string;
    password: string;
    /** Public page of the demo business. */
    catalogUrl: string;
    isDemoUser: boolean;
};
