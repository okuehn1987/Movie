import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { route as routeFn } from 'ziggy-js';
import { ZiggyConfig } from '../ziggy';
import { PageProps as AppPageProps } from './';
import { Canable, CanMethod, Model } from './types';

type Can = (model: Model, method: CanMethod, canContext?: Canable) => boolean;

declare global {
    interface Window {
        Ziggy: ZiggyConfig;
        can: Can;
    }

    const can: Can;
    const route: typeof routeFn;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
        can: Can;
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
