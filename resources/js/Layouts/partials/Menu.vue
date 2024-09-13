<script setup lang="ts">
// import { useOrganizationSettings } from "@/OrganizationTemplates";
import { router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <v-bottom-navigation grow class="bg-layout">
        <template v-if="user">
            <v-btn
                @click="router.get(route('organization.index'))"
                :class="
                    route().current('organization.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small">Organisationen</span></v-btn
            >
            <v-btn
                @click="router.get(route('absenceType.index'))"
                :class="
                    route().current('absenceType.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small"
                    >Abwesenheitsgründe</span
                ></v-btn
            >
            <v-btn
                @click="router.get(route('specialWorkingHoursFactor.index'))"
                :class="
                    route().current('specialWorkingHoursFactor.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small"
                    >Sonderarbeitzeitfaktor</span
                ></v-btn
            >
            <v-btn
                @click="router.get(route('group.index'))"
                :class="
                    route().current('group.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small">Abteilungen</span></v-btn
            >
            <v-btn
                @click="router.get(route('user.index'))"
                :class="
                    route().current('user.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-account-group"></v-icon>
                <span style="font-size: xx-small">Mitarbeiter</span></v-btn
            >
            <v-btn
                @click="router.get(route('workLog.index'))"
                :class="
                    route().current('workLog.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-clock-outline"></v-icon>
                <span style="font-size: xx-small">Arbeitszeiten</span></v-btn
            ><v-btn
                @click="router.get(route('operatingSite.index'))"
                :class="
                    route().current('operatingSite.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-map-marker"></v-icon>
                <span style="font-size: xx-small">Betriebsorte</span></v-btn
            >
            <!-- <v-btn
                v-if="user.role === 'super-admin'"
                @click="router.get(route('organization.index'))"
                :class="
                    route().current('organization.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small">Organisationen</span>
            </v-btn>
            <v-btn
                v-if="user.has_payment_permission"
                @click="router.get(route('payment.index'))"
                :class="
                    route().current('payment.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-currency-eur"></v-icon>
                <span style="font-size: xx-small">Kostenübersicht</span>
            </v-btn>
            <v-btn
                v-if="
                    user.has_consumer_permission &&
                    $page.props.flags.consumer_administration
                "
                @click="router.get(route('consumer.index'))"
                :class="
                    route().current('consumer.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-account-injury"></v-icon>
                <span style="font-size: xx-small"
                    >{{ useOrganizationSettings().consumer_name }}en</span
                >
            </v-btn>
            <v-btn
                v-if="
                    user.has_video_permission &&
                    $page.props.flags.videos_for_consumers
                "
                @click="router.get(route('video.index'))"
                :class="
                    route().current('video.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-video" />
                <span style="font-size: xx-small">Videos</span>
            </v-btn>
            <v-btn
                v-if="user.has_file_permission"
                @click="router.get(route('file.index'))"
                :class="
                    route().current('file.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-file-document-multiple" />
                <span style="font-size: xx-small">PDFs</span>
            </v-btn>
            <v-btn
                v-if="user.has_chatAssistant_permission"
                @click="router.get(route('chatAssistant.index'))"
                :class="
                    route().current('chatAssistant.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-assistant" />
                <span style="font-size: xx-small">Assistenten</span>
            </v-btn>
            <v-btn
                v-if="user.has_chat_permission"
                @click="router.get(route('chat.index'))"
                :class="
                    route().current('chat.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-chat" />
                <span style="font-size: xx-small">Chats</span>
            </v-btn>
            <v-btn
                v-if="user.has_user_permission"
                @click="router.get(route('user.index'))"
                :class="
                    route().current('user.index')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
            >
                <v-icon icon="mdi-account-group" />
                <span style="font-size: xx-small">Nutzerverwaltung</span>
            </v-btn> -->
        </template>
    </v-bottom-navigation>
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
