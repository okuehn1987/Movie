<script setup lang="ts">
import ChatDetails from '@/Pages/Isa/ChatDetails.vue';
import { AppModule, ChatMessage } from '@/types/types';
import { Head, router, usePage, usePoll } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import AppbarActions from './partials/AppbarActions.vue';
import DrawerMenu from './partials/DrawerMenu.vue';
import { DateTime } from 'luxon';
import { showsGlobalMessage } from '@/utils';

const props = defineProps<{
    title: string;
    backurl?: string;
    noInlinePadding?: boolean;
}>();

const page = usePage();
const isMobile = useDisplay().smAndDown;
const showDrawer = ref(!isMobile.value);

const { start, stop } = usePoll(
    1000 * 60 * 2,
    {
        only: ['unreadNotifications'],
    },
    { keepAlive: true },
);
onMounted(() => {
    start();
});
router.on('before', () => {
    stop();
});
router.on('finish', () => {
    start();
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

const showChat = ref(false);
const INITIAL_MESSAGE = {
    id: -999,
    role: 'assistant',
    created_at: DateTime.local().toISO(),
    msg: 'Hallo, ich bin ISA. Wie kann ich Ihnen helfen?',
} as Pick<ChatMessage, 'id' | 'role' | 'created_at' | 'msg'>;

const INITIAL_USER_CHAT = {
    id: -999,
    chat_messages: [INITIAL_MESSAGE],
};

watch(
    () => !!page.props.currentUserChat,
    () => {
        INITIAL_USER_CHAT.chat_messages = [INITIAL_MESSAGE];
    },
);

function openChat() {
    showChat.value = true;
}
function closeChat() {
    showChat.value = false;
}
function deleteChat() {
    router.delete(route('isa.deleteChat', { chat: page.props.currentUserChat?.id }), { preserveState: true, preserveScroll: true });
    showChat.value = true;
}
</script>

<template>
    <v-app>
        <Head :title="title"></Head>
        <v-navigation-drawer color="background" style="border: none" v-model="showDrawer" image="/img/loggedin-background.png">
            <v-list v-if="currentApp">
                <v-list-item @click.stop="setCurrentApp($page.props.currentAppModule == 'tide' ? 'flow' : 'tide')" class="pe-2">
                    <template v-slot:prepend>
                        <div class="posiion-relative" v-if="page.props.appModules.length == 2" style="width: 56px">
                            <img src="/img/Tide-Logo.webp" id="tide-logo" :class="{ ActiveLogo: currentApp.value == 'tide' }" />
                            <v-icon icon="mdi-undo" style="top: 0; position: absolute; rotate: -20deg; scale: 0.8"></v-icon>
                            <img src="/img/Flow-Logo.webp" id="flow-logo" :class="{ ActiveLogo: currentApp.value == 'flow' }" />
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
                    class="text-h6 ps-0 mx-5 px-2 mb-0"
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
        <v-main>
            <v-container
                class="pt-0"
                :style="$page.props.organization.isa_active ? 'padding-bottom: calc(60px + 16px + 4px)' : ''"
                :class="props.noInlinePadding ? 'px-0' : ''"
                fluid
            >
                <v-alert
                    v-if="$page.props.flash.error"
                    type="error"
                    closable
                    class="mb-6"
                    :key="Math.random()"
                    @click:close="showsGlobalMessage = false"
                >
                    {{ $page.props.flash.error }}
                </v-alert>
                <v-alert
                    v-if="$page.props.flash.success"
                    type="success"
                    closable
                    class="mb-6"
                    :key="Math.random()"
                    @click:close="showsGlobalMessage = false"
                >
                    {{ $page.props.flash.success }}
                </v-alert>
                <slot />
            </v-container>
            <div v-if="$page.props.organization.isa_active">
                <v-btn
                    v-show="!showChat"
                    class="chat-button"
                    style="position: fixed; bottom: 20px; right: 20px"
                    type="button"
                    title="Chat öffnen"
                    @click.stop="openChat"
                >
                    <img src="/img/Isa-klein.png" alt="" />
                </v-btn>
                <transition name="chat-pop">
                    <div v-show="showChat" class="chat-space bg-white elevation-12">
                        <div class="chat-header">
                            <div class="d-flex align-center gap-2 chat-button">
                                <img src="/img/Isa-klein.png" role="button" @click.stop="closeChat" alt="ISA Logo" />
                                <v-card-subtitle class="text-h6">ISA</v-card-subtitle>
                            </div>

                            <div style="width: fit-content">
                                <v-btn icon="mdi-window-minimize" color="primary" variant="text" @click.stop="closeChat" title="Chat minimieren" />
                                <v-btn icon="mdi-chat-plus" color="primary" variant="text" @click.stop="deleteChat" title="Neuer Chat" />
                            </div>
                        </div>
                        <div class="chat-body">
                            <ChatDetails
                                :showChat
                                :chat="$page.props.currentUserChat ? $page.props.currentUserChat : INITIAL_USER_CHAT"
                                :reachedMonthlyTokenLimit="$page.props.reachedMonthlyTokenLimit"
                            />
                        </div>
                    </div>
                </transition>
            </div>
        </v-main>
    </v-app>
</template>

<style scoped>
#tide-logo {
    position: absolute;
    z-index: 1;
    width: 16px;
    left: calc(16px + 24px + 4px);
    top: 5px;
    transition: all 0.3s ease;
}

#flow-logo {
    position: absolute;
    z-index: 1;
    width: 16px;
    left: calc(16px + 24px + 4px);
    top: 5px;
    transition: all 0.3s ease;
}

#tide-logo.ActiveLogo,
#flow-logo.ActiveLogo {
    width: 24px;
    left: 16px;
    top: 50%;
    transform: translateY(calc(-50% + 8px));
}

.chat-button {
    max-width: 60px;
    height: 60px;
    border-radius: 10%;
    background-color: rgba(178, 178, 186, 0.9);
    border: none;
    padding: 0;
    z-index: 1000;
}
.chat-button img {
    height: 60px;
    padding-top: 2px;
}

.chat-space {
    position: fixed;
    bottom: 20px;
    right: min(16px, calc((100vw - min(500px, 100vw - 32px)) / 2));
    width: min(500px, 100vw - 32px);
    height: min(650px, 60vh);
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
    transition:
        transform 200ms linear,
        opacity 200ms linear;
}
.chat-pop-enter-from,
.chat-pop-leave-to {
    transform: scale(0.1);
    opacity: 0;
}
</style>
