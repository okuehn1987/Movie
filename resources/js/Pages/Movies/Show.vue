<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { VIconBtn } from 'vuetify/labs/VIconBtn';

// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps<{
    movies: { id: number; title: string; genre: string; actor: string; rating: number; year: number; hidden: boolean }[];
}>();
</script>

<template>
    <Head title="Movies" />

    <AuthenticatedLayout title="Movies">
        <div>
            <v-video
                :start-at="0"
                :volume-props="{ inline: true }"
                class="mx-auto"
                controls-variant="mini"
                height="600"
                max-width="800"
                rounded="lg"
                src="https://cdn.jsek.work/cdn/vt-sunflowers.mp4"
                eager
                pills
            >
                <template v-slot:controls="{ play, pause, playing, progress, skipTo, volume, fullscreen, toggleFullscreen, labels }">
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
                            <v-btn rounded="lg" size="36" color="green" icon @click="volume.value = volume.value > 0 ? 0 : 0.5">
                                <v-icon>
                                    {{ volume.value === 0 ? 'mdi-volume-off' : 'mdi-volume-high' }}
                                </v-icon>
                            </v-btn>

                            <v-slider v-model="volume.value" min="0" max="1" step="0.01" width="100" hide-details />

                            <!-- <v-slider
                                v-model="volume.value"
                                :aria-label="labels['volumeAction']"
                                min="0"
                                max="1"
                                step="0.01"
                                width="100"
                                class="mx-2"
                                @update:model-value="val => (volume.value = val)"
                            /> -->
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
        </div>
    </AuthenticatedLayout>
</template>
