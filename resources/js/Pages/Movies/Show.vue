<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Actor, Comment, Movie } from '@/types/types';
import { ref } from 'vue';
import { DateTime } from 'luxon';
import CreateUpdateForm from './CreateUpdateForm.vue';

defineProps<{
    movie: Movie & { comments: Comment[]; actors: Actor[] };
    actors: Actor[];
}>();

const toggledVolume = ref(false);

const form = useForm({
    comment: '',
});
</script>

<template>
    <AuthenticatedLayout title="Movies">
        <v-row no-gutters>
            <v-col style="min-width: 375px; max-width: 375px; height: 830px">
                <v-sheet style="margin-right: 20px">
                    <v-card max-width="375" height="830">
                        <v-img class="text-white" height="300px" :src="route('thumbnailFile.getThumbnailContent', { thumbnailFile: movie.id })" cover>
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
                        <v-container style="direction: rtl; padding: 2%">
                            <v-dialog max-width="500">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn icon v-bind="activatorProps">
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>
                                </template>

                                <template v-slot:default="{ isActive }">
                                    <v-card title="Edit">
                                        <v-card-text style="padding-bottom: 0px">
                                            <CreateUpdateForm :actors :movie></CreateUpdateForm>
                                        </v-card-text>

                                        <v-card-actions>
                                            <v-spacer></v-spacer>

                                            <v-btn text="Close Dialog" @click="isActive.value = false"></v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </template>
                            </v-dialog>
                        </v-container>
                        <v-list lines="two">
                            <v-list-item>
                                <template v-slot:prepend>
                                    <v-avatar>
                                        <v-icon color="yellow" icon="mdi-star"></v-icon>
                                    </v-avatar>
                                </template>

                                <v-list-item-title>{{ movie.rating }}</v-list-item-title>

                                <template v-slot:append></template>
                            </v-list-item>

                            <v-list-item>
                                <template v-slot:prepend>
                                    <v-avatar>
                                        <v-icon icon="mdi-calendar"></v-icon>
                                    </v-avatar>
                                </template>

                                <v-list-item-title>
                                    {{ DateTime.fromISO(movie.publication_date.toString()).toLocaleString(DateTime.DATE_SHORT) }}
                                </v-list-item-title>
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

                                <v-list-item-subtitle>Actors</v-list-item-subtitle>
                                <v-list-item-title v-for="actor in movie.actors" :key="actor.id">
                                    {{ actor.first_name }} {{ actor.last_name }}
                                </v-list-item-title>
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
                        max-height="438"
                        min-width="275"
                        mx-auto="none"
                        rounded="lg"
                        :src="route('movieFile.getMovieContent', { movieFile: movie.id })"
                        eager
                        :muted="false"
                        pills
                    >
                        <template
                            v-slot:controls="{ play, pause, playing, progress, skipTo, volume, fullscreen, toggleFullscreen, labels, toggleMuted }"
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
                                <v-container style="height: 392px" ; width="800px">
                                    <v-row no-gutters>
                                        <v-col>
                                            <form
                                                @submit.prevent="
                                                    form.post(route('movie.comment.store', { movie: movie.id }), {
                                                        onSuccess: () => form.reset(),
                                                    })
                                                "
                                            >
                                                <v-sheet style="width: 750px; display: flex; flex-direction: row">
                                                    <v-text-field v-model="form.comment" style="width: 650px" label="Comment"></v-text-field>
                                                    <v-sheet style="width: 40px">
                                                        <v-icon-btn color="blue" icon="mdi-message-text" type="submit" />
                                                    </v-sheet>
                                                </v-sheet>
                                            </form>
                                        </v-col>
                                    </v-row>
                                    <v-container class="pa-4" style="max-height: 300px; overflow-y: auto">
                                        <div v-for="comment in movie.comments" :key="comment.id" class="mb-4">
                                            <v-row no-gutters mb-2>
                                                <v-col>
                                                    <v-sheet class="pa-2 font-weight-bold">{{ comment.name }}</v-sheet>
                                                </v-col>
                                                <v-col class="text-right">
                                                    <v-sheet class="pa-2 text-caption grey--text">
                                                        {{ DateTime.fromISO(comment.created_at.toString()).toLocaleString(DateTime.DATE_SHORT) }}
                                                        <p>
                                                            {{
                                                                DateTime.fromISO(comment.created_at.toString()).toLocaleString(
                                                                    DateTime.TIME_24_WITH_SECONDS,
                                                                )
                                                            }}
                                                        </p>
                                                    </v-sheet>
                                                </v-col>
                                            </v-row>

                                            <v-row no-gutters>
                                                <v-col>
                                                    <v-sheet class="pa-3">{{ comment.comment }}</v-sheet>
                                                </v-col>
                                            </v-row>
                                            <v-divider class="my-2"></v-divider>
                                        </div>
                                    </v-container>
                                </v-container>
                            </v-card>
                        </v-sheet>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </AuthenticatedLayout>
</template>
