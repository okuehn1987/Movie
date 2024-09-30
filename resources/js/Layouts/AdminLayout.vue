<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import Menu from "@/Layouts/partials/Menu.vue";

defineProps<{
    title: string;
    backurl?: string;
}>();
</script>

<template>
    <v-app>
        <Head :title="title"></Head>
        <div class="h-100 d-flex flex-column">
            <v-app-bar :elevation="2" color="layout">
                <div
                    class="w-100"
                    style="display: grid; grid-template-columns: 1fr auto 1fr"
                >
                    <div>
                        <v-btn
                            v-if="backurl"
                            variant="plain"
                            icon="mdi-arrow-left"
                            @click.stop="router.get(backurl)"
                        ></v-btn>
                    </div>
                    <div class="d-flex align-center text-center">
                        <v-toolbar-title class="font-weight-bold text-h5">
                            <div class="d-flex align-center">
                                {{ title }}
                            </div>
                        </v-toolbar-title>
                    </div>
                    <div class="d-flex justify-end">
                        <v-btn><v-icon icon="mdi-bell"></v-icon></v-btn>
                        <v-btn class="me-4">
                            <v-icon icon="mdi-menu" size="x-large"></v-icon>
                            <v-menu activator="parent">
                                <v-list min-width="200">
                                    <v-list-item
                                        @click.stop="
                                            router.get(route('profile.edit'))
                                        "
                                        prepend-icon="mdi-account"
                                        >Profil</v-list-item
                                    >
                                    <v-list-item
                                        @click.stop="
                                            router.post(route('logout'))
                                        "
                                        prepend-icon="mdi-logout"
                                        >Abmelden</v-list-item
                                    >
                                </v-list>
                            </v-menu>
                        </v-btn>
                    </div>
                    <slot name="appbarActions" />
                </div>
            </v-app-bar>
            <v-spacer style="flex-basis: 64px; flex-grow: 0; flex-shrink: 0" />
            <slot name="beforeScrollContainer" />
            <div
                id="scrollContainer"
                class="d-flex flex-column"
                style="height: 0; flex: 1; overflow-y: auto"
            >
                <slot />
            </div>
            <Menu v-if="!backurl"></Menu>
        </div>
    </v-app>
</template>

<style>
.v-application__wrap {
    height: 100dvh;
}

#app {
    height: 100%;
}
</style>
