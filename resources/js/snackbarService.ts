import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export const showSnackbar = ref(false);
export const snackbarContent = ref('');

router.on('navigate', () => (showSnackbar.value = false));

export function showSnackbarMessage(text: string | Record<string, string>) {
    showSnackbar.value = false;
    const message = typeof text == 'object' ? Object.values(text).join('\n') : text;
    if (!message) return;
    snackbarContent.value = message;
    showSnackbar.value = true;
}
