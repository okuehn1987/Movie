import { Notification, Organization, User } from './types';

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User & { unread_notifications: Notification[] };
    };
    organization: Organization;
};
