import { DateTime } from "luxon";
import { onMounted, onUnmounted, ref } from "vue";

export function useNow() {
    const now = ref(DateTime.now());
    let interval: number;
    onMounted(
        () => (interval = setInterval(() => (now.value = DateTime.now()), 1000))
    );
    onUnmounted(() => clearInterval(interval));
    return now;
}
