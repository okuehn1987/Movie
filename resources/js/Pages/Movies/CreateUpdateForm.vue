<script setup lang="ts">
const form = useForm({
    title: '',
    genre: '',
    actor: '',
    publicationDate: '',
    movieLength: '',
    rating: '',
    hidden: false,
    description: '',
    movie_file: undefined as File | undefined,
    thumbnail_file: undefined as File | undefined,
});
</script>

<template>
    <!-- {{ movies }} -->
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form @submit.prevent="form.post(route('movie.store'), { onSuccess: () => form.reset() })">
            <v-text-field v-model="form.title" placeholder="Movie Name" :error-messages="form.errors.title"></v-text-field>
            <v-file-input v-model="form.thumbnail_file" label="Movie Cover"></v-file-input>
            <v-text-field v-model="form.genre" placeholder="Gernre" :error-messages="form.errors.genre"></v-text-field>
            <v-text-field v-model="form.actor" placeholder="Actors" :error-messages="form.errors.actor"></v-text-field>
            <v-date-input v-model="form.publicationDate" placeholder="Publication Date" :error-messages="form.errors.publicationDate"></v-date-input>
            <v-text-field v-model="form.description" placeholder="Description" :error-messages="form.errors.description"></v-text-field>
            <v-text-field v-model="form.rating" placeholder="Rating" :error-messages="form.errors.rating"></v-text-field>
            <v-checkbox v-model="form.hidden" label="Hidden" :error-messages="form.errors.hidden"></v-checkbox>
            <v-file-upload style="margin-bottom: 20px" v-model="form.movie_file" density="compact"></v-file-upload>
            <v-btn type="submit">Movie Upload</v-btn>
        </form>
    </div>
</template>
