import { AppModule, Canable, Notification, Organization, User } from './types';

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    unreadNotifications: Notification[];
    organization: Organization;
    currentAppModule: AppModule['module'];
    appModules: { title: string; value: AppModule['module'] }[];
    globalCan: Canable['can'];
    appGlobalCan: Canable['can'];
} & Partial<Canable>;
