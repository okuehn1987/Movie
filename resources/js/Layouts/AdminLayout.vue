<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';
import { useDisplay } from 'vuetify';
import DrawerMenu from './partials/DrawerMenu.vue';
import AppbarActions from './partials/AppbarActions.vue';

defineProps<{
    title: string;
    backurl?: string;
}>();

const isMobile = useDisplay().smAndDown;
const showDrawer = ref(true);
watchEffect(() => (showDrawer.value = !isMobile.value));
</script>

<template>
    <v-app>
        <Head :title="title"></Head>

        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png" :permanent="!isMobile">
            <v-list>
                <v-list-item @click="router.get('/')" class="d-flex flex-row">
                    <h1 class="text-center font-weight-medium">
                        <v-icon icon="mdi-timer-lock" />
                        <span class="ms-3">ShiftButler</span>
                    </h1>
                </v-list-item>
            </v-list>
            <v-divider />
            <DrawerMenu />
        </v-navigation-drawer>

        <v-app-bar elevation="0" color="background">
            <v-app-bar-nav-icon v-if="isMobile" variant="text" @click.stop="showDrawer = !showDrawer"></v-app-bar-nav-icon>

            <v-toolbar-title>
                <v-btn v-if="backurl" variant="plain" icon="mdi-arrow-left" @click.stop="router.get(backurl)"></v-btn>
                {{ title }}
            </v-toolbar-title>

            <slot name="appbarActions" />

            <AppbarActions />
        </v-app-bar>
        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>

<style></style>
