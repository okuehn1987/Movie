import { DateTime } from 'luxon';
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useNow() {
    const now = ref(DateTime.now());
    let interval: number;
    onMounted(() => (interval = setInterval(() => (now.value = DateTime.now()), 1000)));
    onUnmounted(() => clearInterval(interval));
    return now;
}

export function usePageIsLoading() {
    const loading = ref(false);

    router.on('start', () => (loading.value = true));
    router.on('finish', () => (loading.value = false));

    return loading;
}

export function tableHeight(items: unknown[], reduction = 0) {
    return items.length ? `calc(100vh - 152px${reduction ? ' - ' + reduction + 'px' : ''})` : undefined;
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
