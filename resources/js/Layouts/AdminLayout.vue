<script setup lang="ts">
import ChatDetails from '@/Pages/Isa/ChatDetails.vue';
import { AppModule } from '@/types/types';
import { Head, router, usePage, usePoll } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
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
function setCurrentApp(module: AppModule['module']) {
    router.post(route('switchAppModule', { module }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

const currentApp = computed(() => page.props.appModules.find(m => m.value === page.props.currentAppModule));

const showChat = ref(false);
const showIntro = ref(false);

function openChat() {
    showIntro.value = true;
    showChat.value = true;
}
function closeChat() {
    showChat.value = false;
}
function newChat() {
    // TODO: start new chat
}
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
                <span
                    class="text-h6 ps-0 mx-md-5 px-2 mb-0"
                    style="text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: calc(100vw - 72px - 72px - 52px)"
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
        <v-main class="postion-relative">
            <v-container class="pt-0" fluid>
                <v-alert v-if="$page.props.flash.error" type="error" closable class="mb-6" :key="Math.random()">
                    {{ $page.props.flash.error }}
                </v-alert>
                <v-alert v-if="$page.props.flash.success" type="success" closable class="mb-6" :key="Math.random()">
                    {{ $page.props.flash.success }}
                </v-alert>
                <slot />
            </v-container>
            <v-btn
                v-show="!showChat"
                class="chat-button"
                style="position: fixed; bottom: 20px; right: 20px"
                type="button"
                title="Chat öffnen"
                @click="openChat"
                aria-label="Chat öffnen"
            >
                <img src="/img/Isa-klein.png" alt="" />
            </v-btn>
            <transition name="chat-pop">
                <div v-show="showChat" class="chat-space bg-white elevation-12">
                    <div class="chat-header">
                        <v-btn class="chat-button" type="button" @click="closeChat" aria-label="Chat schließen" title="Chat schließen">
                            <img src="/img/Isa-klein.png" alt="" />
                        </v-btn>
                        <v-btn icon="mdi-delete" color="error" density="comfortable" variant="text" @click="newChat" title="Chat löschen" />
                    </div>
                    <div class="chat-body">
                        <ChatDetails
                            :chat="$page.props.currentUserChat"
                            :reachedMonthlyTokenLimit="$page.props.reachedMonthlyTokenLimit"
                            :introMode="showIntro"
                            @first-send="showIntro = false"
                        />
                    </div>
                </div>
            </transition>
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

.chat-button {
    width: 60px;
    height: 60px;
    border-radius: 10%;
    background-color: rgba(178, 178, 186, 0.9);
    border: none;
    padding: 0;
    cursor: pointer;
    z-index: 1000;
}
.chat-button img {
    height: 60px;
    padding-top: 2px;
}

.chat-space {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 500px;
    height: 450px;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    z-index: 1001;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 8px;
    background: var(--v-theme-surface-variant);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.chat-body {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}
.chat-pop-enter-active,
.chat-pop-leave-active {
    transform-origin: bottom right;
    transition: transform 200ms linear, opacity 200ms linear;
}
.chat-pop-enter-from,
.chat-pop-leave-to {
    transform: scale(0.1);
    opacity: 0;
}

@media (max-width: 960px) {
    .chat-space {
        width: 92vw;
        height: 60vh;
    }
}
</style>
