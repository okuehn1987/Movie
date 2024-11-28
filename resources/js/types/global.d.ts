import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { route as routeFn } from 'ziggy-js';
import { ZiggyConfig } from '../ziggy';
import { PageProps as AppPageProps } from './';
import { Can, CanMethod, Model } from './types';

type CanCan = (model: Model, method: CanMethod, context?: Can) => boolean;

declare global {
    interface Window {
        Ziggy: ZiggyConfig;
        can: CanCan;
    }

    const can: CanCan;
    const route: typeof routeFn;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
        can: CanCan;
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
