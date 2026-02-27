<script setup lang="ts">
import { Actor, Movie } from '@/types/types';
import { VDateInput } from 'vuetify/labs/VDateInput';

const props = defineProps<{
    actors: Actor[];
    movie?: Movie & { actors: Actor[] };
}>();

const form = useForm({
    title: props.movie?.title ?? '',
    genre: props.movie?.genre ?? '',
    actors: props.movie?.actors ?? ([] as Actor['id'][]),
    publicationDate: props.movie?.publication_date ?? '',
    rating: props.movie?.rating ?? '',
    hidden: props.movie?.hidden ?? false,
    description: props.movie?.description ?? '',

    movie_file: undefined as File | undefined,
    thumbnail_file: undefined as File | undefined,
});

function submit() {
    let submitMovie = '';
    if (props.movie) {
        submitMovie = 'movie.update';
    } else {
        submitMovie = 'movie.store';
    }
    return submitMovie;
}
</script>

<template>
    <!-- {{ movies }} -->
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        <form @submit.prevent="form.post(route(submit()), { onSuccess: () => form.reset() })">
            <v-text-field v-model="form.title" placeholder="Movie Name" :error-messages="form.errors.title"></v-text-field>
            <v-file-input
                v-model="form.thumbnail_file"
                :label="'Movie Cover ' + (movie?.thumbnail_file_path?.replace('thumbnails/', 'aktuell:') ?? '')"
            ></v-file-input>
            <v-text-field v-model="form.genre" placeholder="Gernre" :error-messages="form.errors.genre"></v-text-field>
            <v-select
                v-model="form.actors"
                label="Actors"
                :items="actors"
                item-value="id"
                :item-title="item => `${item.last_name}, ${item.first_name}`"
                multiple
                variant="underlined"
            ></v-select>
            <v-date-input v-model="form.publicationDate" placeholder="Publication Date" :error-messages="form.errors.publicationDate"></v-date-input>
            <v-text-field v-model="form.description" placeholder="Description" :error-messages="form.errors.description"></v-text-field>
            <v-text-field v-model="form.rating" placeholder="Rating" :error-messages="form.errors.rating"></v-text-field>
            <v-checkbox v-model="form.hidden" label="Hidden" :error-messages="form.errors.hidden"></v-checkbox>
            <v-file-upload v-if="!movie" style="margin-bottom: 20px" v-model="form.movie_file" density="compact"></v-file-upload>
            <v-btn type="submit">{{ !!movie ? 'Save Edit' : 'Add Movie' }}</v-btn>
        </form>
    </div>
</template>
