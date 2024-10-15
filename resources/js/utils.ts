import { DateTime } from 'luxon';
import { onMounted, onUnmounted, ref } from 'vue';
import { router, InertiaForm } from '@inertiajs/vue3';

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

export function copyDataToForm(form: InertiaForm<Record<string, any>>, obj: Record<string, unknown>) {
    for (const key in form.data()) {
        if (key in obj && key in form) form[key] = obj[key];
    }
}
