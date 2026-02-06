<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';

defineProps<{
    movies: { id: number; title: string; genre: string; actor: string; rating: number; year: number; hidden: boolean }[];
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
        <div>
            <form @submit.prevent="form.post(route('movies.store'), { onSuccess: () => form.reset() })">
                <v-container>
                    <v-row no-gutters>
                        <v-col v-for="movie in movies" :key="movie.id" cols="12" sm="2">
                            <v-sheet style="display: flex; flex-direction: column; align-items: center; width: 170px; height: 220px" class="ma-2">
                                <div>{{ movie.title }}</div>
                                <p>
                                    <img
                                        style="width: 150px; height: 180px; object-fit: cover"
                                        :src="route('thumbnailFile.getThumbnailContent', { thumbnailFile: movie.id })"
                                    />
                                </p>
                            </v-sheet>
                        </v-col>
                    </v-row>
                </v-container>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
