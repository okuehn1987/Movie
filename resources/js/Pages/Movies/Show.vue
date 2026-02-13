<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Movie } from '@/types/types';
import { ref } from 'vue';

defineProps<{
    movies: {
        id: number;
        title: string;
        genre: string;
        actor: string;
        rating: number;
        publicationDate: string;
        hidden: boolean;
        description: string;
    }[];
    movie: Movie;
}>();

const toggledVolume = ref(false);
</script>

<template>
    <AuthenticatedLayout title="Movies">
        <v-container style="padding: 0px">
            <v-row no-gutters>
                <v-col style="min-width: 375px; max-width: 375px; height: 830px">
                    <v-sheet>
                        <v-card max-width="375" height="830">
                            <v-img
                                class="text-white"
                                height="300px"
                                :src="route('thumbnailFile.getThumbnailContent', { thumbnailFile: movie.id })"
                                cover
                            >
                                <div class="d-flex flex-column h-100">
                                    <v-card-title class="d-flex ga-2 px-2">
                                        <v-spacer></v-spacer>
                                    </v-card-title>

                                    <v-spacer></v-spacer>

                                    <v-card-title class="pb-6 text-center">
                                        <div class="text-h4">{{ movie.title }}</div>
                                    </v-card-title>
                                </div>
                            </v-img>

                            <v-list lines="two">
                                <v-list-item>
                                    <template v-slot:prepend>
                                        <v-avatar>
                                            <v-icon color="yellow" icon="mdi-star"></v-icon>
                                        </v-avatar>
                                    </template>

                                    <v-list-item-title>{{ movie.rating }}</v-list-item-title>
                                    <!-- <v-list-item-subtitle>Rating</v-list-item-subtitle> -->

                                    <template v-slot:append></template>
                                </v-list-item>

                                <v-list-item>
                                    <template v-slot:prepend>
                                        <v-avatar>
                                            <v-icon icon="mdi-calendar"></v-icon>
                                        </v-avatar>
                                    </template>

                                    <v-list-item-title>{{ movie.publicationDate }}</v-list-item-title>
                                    <!-- <v-list-item-subtitle>Publication Date</v-list-item-subtitle> -->

                                    <template v-slot:append></template>
                                </v-list-item>

                                <v-divider inset></v-divider>

                                <v-list-item>
                                    <template v-slot:prepend>
                                        <v-avatar>
                                            <v-icon icon="mdi-circle"></v-icon>
                                        </v-avatar>
                                    </template>

                                    <v-list-item-title>{{ movie.actor }}</v-list-item-title>
                                    <v-list-item-subtitle>Actors</v-list-item-subtitle>
                                </v-list-item>

                                <v-divider inset></v-divider>

                                <v-list-item>
                                    <template v-slot:prepend></template>

                                    <p>
                                        {{ movie.description }}
                                    </p>
                                </v-list-item>
                            </v-list>
                        </v-card>
                    </v-sheet>
                </v-col>
                <v-col style="min-width: 200px; max-width: 800px; min-height: 112px">
                    <v-sheet>
                        <v-video
                            :start-at="0"
                            :volume-props="{ inline: true }"
                            :volume="50"
                            controls-variant="mini"
                            min-height="154"
                            min-width="275"
                            mx-auto="none"
                            rounded="lg"
                            :src="route('movieFile.getMovieContent', { movieFile: movie.id })"
                            eager
                            :muted="false"
                            pills
                        >
                            <template
                                v-slot:controls="{
                                    play,
                                    pause,
                                    playing,
                                    progress,
                                    skipTo,
                                    volume,
                                    fullscreen,
                                    toggleFullscreen,
                                    labels,
                                    toggleMuted,
                                }"
                            >
                                <v-defaults-provider
                                    :defaults="{
                                        VIconBtn: { color: 'green', rounded: 'lg', size: '36', variant: 'flat' },
                                        VSlider: { color: 'orange', trackColor: 'white' },
                                    }"
                                >
                                    <div class="d-flex ga-3 w-100 px-2">
                                        <v-icon-btn
                                            :aria-label="labels['playAction']"
                                            :icon="playing ? 'mdi-pause' : 'mdi-play'"
                                            v-tooltip:top="labels['playAction']"
                                            @click="() => (playing ? pause() : play())"
                                        ></v-icon-btn>
                                        <v-slider
                                            :aria-label="labels['seek']"
                                            :model-value="progress"
                                            width="75%"
                                            no-keyboard
                                            @update:model-value="skipTo"
                                        ></v-slider>
                                        <v-video-volume
                                            v-model="volume.value"
                                            :slider-props="{ maxWidth: 100, width: '25%' }"
                                            class="ga-3"
                                            inline
                                            @click="
                                                () => {
                                                    toggleMuted();
                                                    toggledVolume = true;
                                                }
                                            "
                                        ></v-video-volume>
                                        <v-icon-btn
                                            :aria-label="labels['fullscreenAction']"
                                            :icon="fullscreen ? '$fullscreenExit' : '$fullscreen'"
                                            v-tooltip:top="labels['fullscreenAction']"
                                            @click="toggleFullscreen"
                                        ></v-icon-btn>
                                    </div>
                                </v-defaults-provider>
                            </template>
                        </v-video>
                    </v-sheet>
                    <v-row>
                        <v-col>
                            <v-sheet style="height: 380px">
                                <v-card>
                                    <v-container style="height: 380px">
                                        <v-textarea
                                            class="mx-auto"
                                            label="Comment"
                                            max-width="500"
                                            persistent-placeholder
                                            placeholder="Schreib einen Kommentar"
                                            variant="outlined"
                                        />
                                    </v-container>
                                </v-card>
                            </v-sheet>
                        </v-col>
                    </v-row>
                </v-col>
            </v-row>
        </v-container>
    </AuthenticatedLayout>
</template>
