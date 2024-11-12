import { DateTime } from 'luxon';
import { computed, onMounted, onUnmounted, ref, watch, Ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Paginator } from './types/types';

export function useNow() {
    const now = ref(DateTime.now());
    let interval: number;
    onMounted(() => (interval = setInterval(() => (now.value = DateTime.now()), 1000)));
    onUnmounted(() => clearInterval(interval));
    return now;
}

export function usePageIsLoading() {
    const loading = ref(false);
    const page = ref(route().current());

    router.on('start', e => {
        if (!page.value) return;
        if (route(page.value) == e.detail.visit.url.origin + e.detail.visit.url.pathname) loading.value = true;
    });
    router.on('finish', () => (loading.value = false));

    return loading;
}

export function usePagination<
    TProps extends Record<TKey, Ref<Paginator<TData>>>,
    TKey extends keyof TProps & string,
    TData = TProps extends Record<TKey, Ref<Paginator<infer Data>>> ? Data : unknown,
>(props: TProps, key: TKey, routeProps: Record<string, unknown> = {}) {
    const currentPage = ref(props[key].value.current_page);
    const lastPage = computed(() => props[key].value.last_page);
    const data = computed(() => props[key].value.data);

    watch(currentPage, () => {
        const currentRoute = route().current();
        if (!currentRoute) return;
        router.visit(
            route(currentRoute, {
                page: currentPage.value,
                ...routeProps,
            }),
            {
                only: [key],
            },
        );
    });

    return { currentPage, lastPage, data };
}

/**
 *
 * @param obj The object to fill nullish values in
 * @param fillValue  The value to fill nullish values with
 * @returns The object with filled nullish values
 *
 * @example fillNullishValues({ a: 1, b: null, c: undefined }) // { a: 1, b: '/', c: '/' }
 * @example
 * ```vue
 * <v-data-table-virtual :items="item.users.map(u => fillNullishValues(u))"></v-data-table-virtual>
 * ```
 */
export function fillNullishValues<T extends Record<string, unknown>, Default extends string = '/'>(obj: T, fillValue: Default | '/' = '/') {
    return {
        ...obj,
        ...Object.keys(obj)
            .filter(key => obj[key] == undefined || obj[key] == null)
            .reduce((a, c) => ({ ...a, [c]: fillValue }), {}),
    } as {
        [K in keyof T]: null extends T[K]
            ? Exclude<T[K], null | undefined> | Default
            : undefined extends T[K]
            ? Exclude<T[K], null | undefined> | Default
            : T[K];
    };
}
