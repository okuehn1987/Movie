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

export function useParams() {
	const params = new URLSearchParams(location.search);

	const obj = {} as Record<string, string>;
	for (const [key, value] of params.entries()) obj[key] = value;

	return obj;
}
