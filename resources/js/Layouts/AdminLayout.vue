<script setup lang="ts">
import { Head, router, usePage, usePoll } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import AppbarActions from './partials/AppbarActions.vue';
import { AppModule } from '@/types/types';
import DrawerMenu from './partials/DrawerMenu.vue';

defineProps<{
    title: string;
    backurl?: string;
}>();

const page = usePage();
const isMobile = useDisplay().smAndDown;
const showDrawer = ref(!isMobile.value);

usePoll(10000, {
    only: ['unreadNotifications'],
});

const showOrgImg = ref(!!page.props.organization.logo);
watch(
    () => page.props.organization.logo,
    () => {
        showOrgImg.value = false;
        nextTick(() => (showOrgImg.value = true));
    },
);
function setCurrentApp(module: AppModule['module']) {
    router.post(route('switchAppModule', { module }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const currentApp = computed(() => page.props.appModules.find(m => m.value === page.props.currentAppModule));
</script>

<template>
    <v-app>
        <Head :title="title"></Head>

        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png">
            <v-list v-if="currentApp">
                <v-list-item @click.stop="setCurrentApp($page.props.currentAppModule == 'herta' ? 'timesheets' : 'herta')" class="pe-2">
                    <template v-slot:prepend>
                        <div class="posiion-relative" v-if="page.props.appModules.length == 2" style="width: 56px">
                            <img src="/img/logo-symbol.png" id="herta-logo" :class="{ ActiveLogo: currentApp.value == 'herta' }" />
                            <v-icon icon="mdi-undo" style="top: 0; position: absolute; rotate: -20deg; scale: 0.8"></v-icon>
                            <img src="/img/TS_Logo.png" id="timesheets-logo" :class="{ ActiveLogo: currentApp.value == 'timesheets' }" />
                            <v-icon icon="mdi-undo" style="top: 25px; left: 40px; position: absolute; rotate: 140deg; scale: 0.8"></v-icon>
                        </div>
                        <template v-else>
                            <v-img width="24px" class="me-8" src="/img/logo-symbol.png"></v-img>
                        </template>
                    </template>
                    <v-list-item-title>
                        <div class="d-flex align-center">
                            <h1 class="text-center text-h5 font-weight-medium">
                                <span style="user-select: none">{{ currentApp.title }}</span>
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
                <span class="text-h6 pl-0 ml-5 mb-0" style="white-space: nowrap">{{ title }}</span>
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

<style scoped>
#herta-logo {
    position: absolute;
    z-index: 1;
    width: 16px;
    left: calc(16px + 24px + 4px);
    top: 5px;
    transition: all 0.3s ease;
}

#timesheets-logo {
    position: absolute;
    z-index: 1;
    width: 16px;
    left: calc(16px + 24px + 4px);
    top: 5px;
    transition: all 0.3s ease;
}

#herta-logo.ActiveLogo,
#timesheets-logo.ActiveLogo {
    width: 24px;
    left: 16px;
    top: 50%;
    transform: translateY(calc(-50% + 8px));
}
</style>
