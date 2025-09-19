<script setup lang="ts">
import { Head, router, usePage, usePoll } from '@inertiajs/vue3';
import { nextTick, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import AppbarActions from './partials/AppbarActions.vue';
import DrawerMenu from './partials/DrawerMenu.vue';

defineProps<{
    title: string;
    backurl?: string;
}>();

const page = usePage();
const isMobile = useDisplay().smAndDown;
const showDrawer = ref(!isMobile.value);
const appname = import.meta.env['VITE_APP_NAME'];

usePoll(
    1000 * 60 * 2,
    {
        only: ['unreadNotifications'],
    },
    { keepAlive: true },
);

const showOrgImg = ref(!!page.props.organization.logo);
watch(
    () => page.props.organization.logo,
    () => {
        showOrgImg.value = false;
        nextTick(() => (showOrgImg.value = true));
    },
);
</script>

<template>
    <v-app>
        <Head :title="title"></Head>

        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png">
            <v-list>
                <v-list-item @click.stop="router.get('/')">
                    <template v-slot:prepend>
                        <v-img src="/img/logo-symbol.png" style="width: 24px" class="me-8"></v-img>
                    </template>
                    <v-list-item-title>
                        <div class="d-flex align-center">
                            <h1 class="text-center font-weight-medium">
                                <span>{{ appname }}</span>
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
            <v-app-bar-nav-icon
                v-if="backurl"
                variant="text"
                icon="mdi-arrow-left"
                @click.stop="router.visit(backurl, { preserveState: true })"
            ></v-app-bar-nav-icon>

            <div class="d-flex align-center mr-auto ga-2">
                <span
                    class="text-h6 ps-0 mx-md-5 px-2 mb-0"
                    style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; "
                    :style="{
                        maxWidth: $page.props.unreadNotifications.length > 0 ? 'calc(100vw - 72px - 72px - 52px)' : 'calc(100vw -  72px - 52px)',
                    }"
                >
                    {{ title }}
                </span>
                <v-img
                    v-if="showOrgImg"
                    :src="route('organization.getLogo', { organization: $page.props.organization.id, key: $page.props.organization.logo })"
                    max-height="48px"
                    max-width="48px"
                    width="100%"
                    height="100%"
                    class="rounded"
                />
            </div>

            <slot name="appbarActions" />

            <AppbarActions />
        </v-app-bar>
        <v-main>
            <v-container class="pt-0 px-sm-2 px-1" fluid>
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
