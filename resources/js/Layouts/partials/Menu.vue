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
            <v-btn
                @click="router.get(route('organization.show'))"
                :class="
                    route().current('organization.show')
                        ? 'text-white'
                        : 'text-grey-lighten-1'
                "
                style="min-width: 0"
                class="px-1"
                ><v-icon icon="mdi-domain"></v-icon>
                <span style="font-size: xx-small">Organisation</span></v-btn
            >
        </template>
    </v-bottom-navigation>
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
