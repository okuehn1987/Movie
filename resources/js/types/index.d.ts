import { Canable, Notification, Organization, User } from './types';

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    unreadNotifications: Notification[];
    organization: Organization;
    globalCan: Canable['can'];
} & Partial<Canable>;
