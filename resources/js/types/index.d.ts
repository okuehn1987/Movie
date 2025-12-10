import { AppModule, Canable, ChatMessage, Notification, Organization, User } from './types';

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    unreadNotifications: Notification[];
    organization: Organization;
    reachedMonthlyTokenLimit: boolean;
    currentUserChat:
        | (Pick<Chat, 'id'> & {
              chat_messages: Pick<ChatMessage, 'id' | 'role' | 'created_at' | 'msg'>[];
          })
        | null;
    currentAppModule: AppModule['module'];
    appModules: { title: string; value: AppModule['module'] }[];
    globalCan: Canable['can'];
    appGlobalCan: Canable['can'];
} & Partial<Canable>;
