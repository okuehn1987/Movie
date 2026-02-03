<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useDisplay } from 'vuetify';
import AppbarActions from './partials/AppbarActions.vue';
import DrawerMenu from './partials/DrawerMenu.vue';

defineProps<{
    title: string;
    backurl?: string;
    backurlTitle?: string;
}>();

const isMobile = useDisplay().smAndDown;
const showDrawer = ref(!isMobile.value);
</script>

<template>
    <v-app>
        <Head :title="title"></Head>
        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png">
            <v-list>
                <v-list-item class="pe-2">
                    <template v-slot:prepend>
                        <v-img width="24px" class="me-8" src="/img/logo-symbol.png"></v-img>
                    </template>
                    <v-list-item-title>
                        <div class="d-flex align-center">
                            <h1 class="text-center text-h5 font-weight-medium">
                                <span style="user-select: none">Movie</span>
                            </h1>
                        </div>
                    </v-list-item-title>
                    <template #append>
                        <v-btn @click.stop="showDrawer = false" icon="mdi-close" variant="text" />
                    </template>
                </v-list-item>
            </v-list>
            <v-divider />
            <DrawerMenu />
        </v-navigation-drawer>

        <v-app-bar elevation="0" color="background">
            <v-app-bar-nav-icon v-if="!showDrawer || isMobile" variant="text" @click.stop="showDrawer = !showDrawer"></v-app-bar-nav-icon>
            <span class="ms-1 d-flex justify-content-center align-center" v-if="backurl">
                <template v-if="backurlTitle">
                    <v-btn
                        prepend-icon="mdi-arrow-left"
                        color="info"
                        variant="flat"
                        size="small"
                        class="ms-3 text-subtitle-2"
                        @click.stop="router.visit(backurl, { preserveState: true })"
                    >
                        {{ backurlTitle }}
                    </v-btn>
                </template>
                <v-app-bar-nav-icon
                    v-else
                    variant="text"
                    icon="mdi-arrow-left"
                    @click.stop="router.visit(backurl, { preserveState: true })"
                ></v-app-bar-nav-icon>
            </span>

            <div class="d-flex align-center mr-auto ga-2">
                <span
                    class="text-h6 ps-0 mx-5 px-2 mb-0"
                    style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: calc(100vw - 72px - 72px - 52px)"
                >
                    {{ title }}
                </span>
            </div>

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
