<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';
import { useDisplay } from 'vuetify';
import AppbarActions from './partials/AppbarActions.vue';
import DrawerMenu from './partials/DrawerMenu.vue';

defineProps<{
    title: string;
    backurl?: string;
}>();

const isMobile = useDisplay().smAndDown;
const showDrawer = ref(true);
watchEffect(() => (showDrawer.value = !isMobile.value));
const appname = import.meta.env['VITE_APP_NAME'];
</script>

<template>
    <v-app>
        <Head :title="title"></Head>

        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png" :permanent="!isMobile">
            <v-list>
                <v-list-item @click="router.get('/')" class="d-flex flex-row">
                    <h1 class="text-center font-weight-medium">
                        <v-icon icon="mdi-timer-lock" />
                        <span class="ms-3">{{ appname }}</span>
                    </h1>
                </v-list-item>
            </v-list>
            <v-divider />
            <DrawerMenu />
        </v-navigation-drawer>

        <v-app-bar elevation="0" color="background">
            <v-app-bar-nav-icon v-if="backurl" variant="text" icon="mdi-arrow-left" @click.stop="router.get(backurl)"></v-app-bar-nav-icon>
            <v-app-bar-nav-icon v-else-if="isMobile" variant="text" @click.stop="showDrawer = !showDrawer"></v-app-bar-nav-icon>

            <v-toolbar-title>
                {{ title }}
            </v-toolbar-title>

            <slot name="appbarActions" />

            <AppbarActions />
        </v-app-bar>
        <v-main>
            <v-container class="pt-0" fluid>
                <v-alert v-if="$page.props.flash.error" type="error" closable class="mb-6" :key="Math.random()">
                    {{ $page.props.flash.error }}
                </v-alert>
                <v-alert v-if="$page.props.flash.success" type="success" closable class="mb-6" :key="Math.random()">
                    {{ $page.props.flash.success }}
                </v-alert>
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>

<style></style>
