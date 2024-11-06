import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { route as routeFn } from 'ziggy-js';
import { ZiggyConfig } from '../ziggy';
import { PageProps as AppPageProps } from './';

declare global {
    interface Window {
        Ziggy: ZiggyConfig;
    }

    const route: typeof routeFn;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {
        flash: {
            error: string | null;
            success: string | null;
        };
    }
}
