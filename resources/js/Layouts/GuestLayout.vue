<script setup lang="ts">
import { Head } from "@inertiajs/vue3";

defineProps<{
    title: string;
}>();
</script>

<template>
    <v-app>
        <Head :title="title"></Head>
        <div class="h-100 d-flex align-center flex-column text-white">
            <div
                class="w-100 d-flex flex-column align-center justify-center"
                style="flex: 1; height: 0"
            >
                <div
                    class="w-100 px-6 d-flex justify-center"
                    style="
                        flex: 1;
                        height: 0;
                        overflow: auto;
                        max-width: 600px;
                        padding-top: calc(8vh + 10px);
                        margin-top: -8vh;
                    "
                >
                    <slot />
                </div>
            </div>
            <slot name="bottom" />
        </div>
    </v-app>
</template>

<style>
.v-application__wrap {
    height: 100dvh;
}
.v-bottom-navigation {
    position: absolute !important;
}
#app {
    height: 100%;
}
</style>
