<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';

defineProps<{
    movies: { id: number; title: string; genre: string; actor: string; rating: number; year: number; hidden: boolean; duration_in_seconds: number }[];
}>();

const form = useForm({});

function secondsToMinutes(seconds: number) {
    const minutes = Math.floor(Math.round(seconds) / 60);
    const remainingSeconds = Math.round(seconds) % 60;

    const formattedMinutes = String(minutes).padStart(2, '0');
    const formattedSeconds = String(remainingSeconds).padStart(2, '0');

    return `${formattedMinutes}:${formattedSeconds}`;
}
</script>

<template>
    <Head title="Movies" />

    <AuthenticatedLayout title="Movies">
        <!-- {{ movies }} -->
        <div>
            <form @submit.prevent="form.post(route('movies.store'), { onSuccess: () => form.reset() })">
                <v-container>
                    <v-row>
                        <v-col v-for="movie in movies" :key="movie.id" cols="12" md="2">
                            <v-sheet style="display: flex; flex-direction: column; max-width: 150px; height: 220px; width: 100%" class="ma-2">
                                <Link style="position: relative" :href="route('movies.show', { movie: movie.id })">
                                    <div>
                                        {{ movie.title }}
                                    </div>
                                    <img
                                        style="width: 100%; height: 180px; object-fit: cover"
                                        :src="route('thumbnailFile.getThumbnailContent', { thumbnailFile: movie.id })"
                                    />
                                    <span
                                        style="
                                            width: 50px;
                                            display: flex;
                                            justify-content: center;
                                            align-items: center;
                                            color: white;
                                            background-color: rgba(0, 0, 0, 0.5);
                                            border-radius: 10%;
                                            position: absolute;
                                            bottom: 15px;
                                            right: 15px;
                                        "
                                    >
                                        {{ secondsToMinutes(movie.duration_in_seconds) }}
                                    </span>
                                </Link>
                            </v-sheet>
                        </v-col>
                    </v-row>
                </v-container>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
