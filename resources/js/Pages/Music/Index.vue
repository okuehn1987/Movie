<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

defineProps<{
    music: { id: number; artist: string; album: string; genre: string; year: number };
}>();

const form = useForm({
    artist: '',
    album: '',
    genre: '',
    year: '',
});
</script>

<template>
    <Head title="Music" />

    <AuthenticatedLayout title="Music">
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('music.store'), { onSuccess: () => form.reset() })">
                <v-text-field v-model="form.artist" placeholder="Artist"></v-text-field>
                <v-text-field v-model="form.album" placeholder="Album"></v-text-field>
                <v-text-field v-model="form.genre" placeholder="Genre"></v-text-field>
                <v-text-field v-model="form.year" placeholder="Year"></v-text-field>
                <v-btn type="submit">Hinzufügen</v-btn>
                {{ form.errors }}
            </form>
        </div>
    </AuthenticatedLayout>
</template>
