import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { route as routeFn } from 'ziggy-js';
import { ZiggyConfig } from '../ziggy';
import { PageProps as AppPageProps } from './';
import { Canable, CanMethod, Model } from './types';
import { FormReturnType } from '@/useFormPlugin';

type Can = (model: Model, method: CanMethod, canContext?: Canable) => boolean;
type UseForm = <TForm extends object>(data: TForm) => FormReturnType<TForm>;

declare global {
    interface Window {
        Ziggy: ZiggyConfig;
        can: Can;
        useForm: UseForm;
    }

    const can: Can;
    const route: typeof routeFn;
    const useForm: UseForm;
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
