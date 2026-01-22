import { router, usePage } from '@inertiajs/vue3';
import { DateTime, Duration } from 'luxon';
import { computed, onMounted, onUnmounted, ref, Ref, watch } from 'vue';
import { Country, Paginator, FederalState, TimeAccountSetting, Tree, Address, Notification } from './types/types';

export function useNow() {
    const now = ref(DateTime.now());
    let interval: NodeJS.Timeout;
    onMounted(() => (interval = setInterval(() => (now.value = DateTime.now()), 1000)));
    onUnmounted(() => clearInterval(interval));
    return now;
}

export const showsGlobalMessage = ref(false);

export function useMaxScrollHeight(extraHeight: number) {
    const page = usePage();
    const height = ref(`calc(100vh - ${80 + extraHeight}px)`);
    watch(page, () => {
        if (page.props.flash.error || page.props.flash.success) showsGlobalMessage.value = true;
        else showsGlobalMessage.value = false;
    });
    watch(showsGlobalMessage, () => {
        if (showsGlobalMessage.value) height.value = `calc(100vh - ${80 + 88 + extraHeight}px)`;
        else height.value = `calc(100vh - ${80 + extraHeight}px)`;
    });
    return height;
    //  80px = toolbar height + padding-bottom
    //  88px = flash message height
}

export function getMaxScrollHeight(extraHeight: number) {
    //  80px = toolbar height + padding-bottom
    return `calc(100vh - ${80 + extraHeight}px)`;
}

export function usePageIsLoading() {
    const loading = ref(false);
    const page = ref(route().current());

    router.on('start', e => {
        if (!page.value) return;
        if (route(page.value, route().params) == e.detail.visit.url.origin + e.detail.visit.url.pathname) loading.value = true;
    });
    router.on('finish', () => (loading.value = false));

    return loading;
}

export function usePagination<
    TProps extends Record<TKey, Ref<Paginator<TData>>>,
    TKey extends keyof TProps & string,
    TData = TProps extends Record<TKey, Ref<Paginator<infer Data>>> ? Data : unknown,
>(props: TProps, key: TKey, routeProps: Record<string, unknown> = {}, reloadKey?: string) {
    const currentPage = ref(props[key].value.current_page);
    const lastPage = computed(() => props[key].value.last_page);
    const data = computed(() => props[key].value.data);
    const itemsPerPage = computed(() => props[key].value.per_page);

    watch(currentPage, () => {
        const currentRoute = route().current();
        if (!currentRoute) return;
        router.visit(
            route(currentRoute, {
                ...route().params,
                page: currentPage.value,
                ...routeProps,
            }),
            {
                only: [reloadKey ?? key],
            },
        );
    });

    return { currentPage, lastPage, data, itemsPerPage };
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

export function getTruncationCycleDisplayName(cycleLength: TimeAccountSetting['truncation_cycle_length_in_months']) {
    return ({ 'null': 'Unbegrenzt', '1': 'Monatlich', '3': 'Quartalsweise', '6': 'Halbjährlich', '12': 'Jährlich' } as const)[cycleLength ?? 'null'];
}

export const DEFAULT_ACCOUNTYPE_NAME = 'Gleitzeitkonto';
export function accountType(type: TimeAccountSetting['type']): typeof DEFAULT_ACCOUNTYPE_NAME | (string & NonNullable<unknown>) {
    return type ?? DEFAULT_ACCOUNTYPE_NAME;
}

export function roundTo(value: number, decimalPlaces: number) {
    return Math.round(value * 10 ** decimalPlaces) / 10 ** decimalPlaces;
}

export function filterTree<K extends keyof T, T extends { [x in K]?: T[] }>(tree: T[], k: K, fn: (e: Omit<T, K>) => boolean): T[] {
    return tree
        .map(e => {
            if (e[k] && e[k].length === 0) return { ...e, [k]: undefined };
            if (e[k]) {
                const children = filterTree(e[k], k, fn);
                return children.length > 0 ? { ...e, [k]: children } : null;
            }
            return fn(e) ? e : null;
        })
        .filter(e => e != null);
}

export const mapTree = <K extends string & keyof T, T extends { [x in K]?: T[] }, MappedType>(
    tree: T[],
    k: K,
    fn: (e: Omit<T, K>, level: number) => MappedType,
    level: number = 0,
): Tree<MappedType, K>[] => {
    return tree.map(e => ({ ...fn(e, level), [k]: e[k] ? mapTree(e[k], k, fn, level + 1) : [] } as Tree<MappedType, K>));
};

export function getStates(country: Country, countries: { title: string; value: Country; regions: Record<FederalState, string> }[]) {
    return Object.entries(countries.find(c => c.value === country)?.regions ?? []).map(([k, v]) => ({
        title: v,
        value: k,
    }));
}

export function debounce<T extends (...args: unknown[]) => void>(func: T, delay: number): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout>;

    return function (...args: Parameters<T>) {
        // Clear the previous timeout if the function is called again
        if (timeout) {
            clearTimeout(timeout);
        }

        // Set a new timeout to call the function after the specified delay
        timeout = setTimeout(() => {
            func(...args);
        }, delay);
    };
}

export function throttle<T extends (...args: unknown[]) => void>(func: T, delayLimit: number): (...args: Parameters<T>) => void {
    let lastFunc: ReturnType<typeof setTimeout>;
    let lastRan: number = 0;

    return function (...args: Parameters<T>) {
        const now = Date.now();

        // If enough time has passed, run the function
        if (now - lastRan >= delayLimit) {
            func(...args);
            lastRan = now;
        } else {
            // Otherwise, set a timeout to call the function after the limit period
            if (lastFunc) clearTimeout(lastFunc);
            lastFunc = setTimeout(() => {
                func(...args);
                lastRan = now;
            }, delayLimit - (now - lastRan));
        }
    };
}

export function secondsToDuration(seconds: number): `${string}:${string}` {
    return `${Math.floor(seconds / 3600)
        .toString()
        .padStart(2, '0')}:${((seconds % 3600) / 60).toString().padStart(2, '0')}`;
}

export function formatDuration(seconds: number, accuracy: 'seconds' | 'minutes' = 'seconds', formatType: 'time' | 'duration' = 'time') {
    const format = {
        seconds: formatType == 'time' ? 'h:mm:ss' : `h'h 'mm'm 'ss's'`,
        minutes: formatType == 'time' ? 'h:mm' : `h'h 'mm'm'`,
    };

    return (seconds < 0 ? '-' : '') + Duration.fromObject({ seconds: Math.abs(seconds) }).toFormat(format[accuracy]);
}

export function formatAddress(obj: Pick<Address, 'street' | 'house_number' | 'zip' | 'city' | 'country' | 'federal_state'>) {
    const a = obj.street && obj.house_number ? obj.street + ' ' + obj.house_number : '';
    const b = obj.zip && obj.city ? obj.zip + ' ' + obj.city : '';
    return [a, b].filter(e => !!e).join(', ');
    //FIXME: handle null cases
}

type Browser = 'Google Chrome' | 'Microsoft Edge' | 'Mozilla Firefox' | 'Apple Safari';
export function getBrowser(): Browser {
    const userAgent = navigator.userAgent;
    let browser = 'Google Chrome' as Browser;

    if (/Chrome/.test(userAgent) && !/Chromium/.test(userAgent)) {
        browser = 'Google Chrome';
    } else if (/Edg/.test(userAgent)) {
        browser = 'Microsoft Edge';
    } else if (/Firefox/.test(userAgent)) {
        browser = 'Mozilla Firefox';
    } else if (/Safari/.test(userAgent)) {
        browser = 'Apple Safari';
    }

    return browser;
}

export function getNotificationUrl(notification: Notification) {
    const page = usePage();

    if (notification.type == 'App\\Notifications\\WorkLogPatchNotification')
        return route('dispute.index', { openPatch: notification.data.work_log_patch_id });
    else if (notification.type == 'App\\Notifications\\WorkLogNotification')
        return route('dispute.index', { openWorkLog: notification.data.work_log_id });
    else if (notification.type == 'App\\Notifications\\AbsenceNotification')
        return route('dispute.index', { openAbsence: notification.data.absence_id });
    else if (notification.type == 'App\\Notifications\\AbsencePatchNotification')
        return route('dispute.index', { openAbsencePatch: notification.data.absence_patch_id });
    else if (notification.type == 'App\\Notifications\\AbsenceDeleteNotification')
        return route('dispute.index', { openAbsenceDelete: notification.data.absence_id });
    else if (notification.type == 'App\\Notifications\\HomeOfficeDayDisputeStatusNotification')
        return route('absence.index', { date: DateTime.fromISO(notification.data.start).toFormat('yyyy-MM') });
    else if (notification.type == 'App\\Notifications\\HomeOfficeDayNotification')
        return route('dispute.index', { openHomeOfficeDay: notification.data.home_office_day_generator_id });
    else if (notification.type == 'App\\Notifications\\HomeOfficeDeleteNotification')
        return route('dispute.index', { openHomeOfficeDayDelete: notification.data.home_office_day_id });
    else if (
        'ticket_id' in notification.data &&
        [
            'App\\Notifications\\TicketCreationNotification',
            'App\\Notifications\\TicketRecordCreationNotification',
            'App\\Notifications\\TicketUpdateNotification',
            'App\\Notifications\\TicketDeletionNotification',
            'App\\Notifications\\TicketFinishNotification',
        ].includes(notification.type)
    )
        return route('ticket.index', { openTicket: notification.data.ticket_id });
    else if (notification.type == 'App\\Notifications\\DisputeStatusNotification') {
        if (notification.data.type == 'delete') return '';
        if (notification.data.log_model == 'App\\Models\\Absence') return route('absence.index', { openAbsence: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\AbsencePatch')
            return route('absence.index', { openAbsencePatch: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\WorkLogPatch')
            return route('user.workLog.index', { user: page.props.auth.user.id, openWorkLogPatch: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\WorkLog')
            return route('user.workLog.index', { user: page.props.auth.user.id, workLog: notification.data.log_id });
    }
    return '';
}

export function useClickHandler() {
    const singleClickTimer = ref<NodeJS.Timeout | null>(null);
    const handlerState = ref<unknown>();

    function clickHandler(singleClickFunction: () => void, doubleClickFunction: (state?: unknown) => void, stateToAdd: unknown, delay: number = 200) {
        if (!singleClickTimer.value) {
            handlerState.value = stateToAdd;
            singleClickTimer.value = setTimeout(() => {
                singleClickFunction();
                singleClickTimer.value = null;
            }, delay);
        } else {
            doubleClickFunction(handlerState.value);
            clearTimeout(singleClickTimer.value);
            singleClickTimer.value = null;
        }
    }

    return { clickHandler };
}
