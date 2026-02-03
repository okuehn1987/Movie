<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

defineProps<{
    movies: { id: number; name: string; genre: string; year: number }[];
}>();

const form = useForm({
    name: '',
    genre: '',
    year: '',
});
</script>

<template>
    <Head title="Movies" />

    <AuthenticatedLayout title="Movies">
        <!-- {{ movies }} -->
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('movies.store'), { onSuccess: () => form.reset() })"></form>
        </div>
    </AuthenticatedLayout>
</template>
