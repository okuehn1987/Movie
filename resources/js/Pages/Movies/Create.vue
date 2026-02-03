<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    genre: '',
    actor: '',
    publicationDate: '',
    movieLength: '',
    rating: '',
    hidden: '',
});
</script>

<template>
    <Head title="Movies" />

    <AuthenticatedLayout title="Movies">
        <!-- {{ movies }} -->
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('movies.store'), { onSuccess: () => form.reset() })">
                <v-text-field v-model="form." placeholder="Movie Name" :error-messages="form.errors.name"></v-text-field>
                <v-text-field v-model="form.genre" placeholder="Gernre" :error-messages="form.errors.genre"></v-text-field>
                <v-text-field v-model="form.genre" placeholder="Actors" :error-messages="form.errors.genre"></v-text-field>
                <v-text-field v-model="form.genre" placeholder="Movielength" :error-messages="form.errors.genre"></v-text-field>
                <v-text-field v-model="form.genre" placeholder="Rating" :error-messages="form.errors.genre"></v-text-field>
                <v-checkbox label="Hidden"></v-checkbox>
                <v-btn type="submit">Einfügen</v-btn>
                <!-- {{ form.errors }} -->
            </form>
            <v-data-table-virtual style="margin-top: 20px" :items="movies"></v-data-table-virtual>
        </div>
    </AuthenticatedLayout>
</template>
