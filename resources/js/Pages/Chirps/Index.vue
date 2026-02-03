<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    message: '',
});
</script>

<template>
    <Head title="Chirps" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('chirps.store'), { onSuccess: () => form.reset() })">
                <v-textarea v-model="form.message" placeholder="What's on your mind?"></v-textarea>
                <InputError :message="form.errors.message" class="mt-2" />
                <v-btn type="submit">Chirp</v-btn>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
