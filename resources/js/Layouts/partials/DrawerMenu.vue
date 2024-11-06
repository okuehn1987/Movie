<script setup lang="ts">
// import { useOrganizationSettings } from "@/OrganizationTemplates";
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <v-list nav :selected="''" @update:selected="([route]) => router.get(route)">
        <template v-if="user">
            <v-list-item
                v-if="user.role === 'super-admin'"
                :active="route().current('organization.index')"
                :value="route('organization.index')"
                prepend-icon="mdi-security"
                title="Organisationen"
            />
            <v-list-item :active="route().current('dashboard')" :value="route('dashboard')" prepend-icon="mdi-view-dashboard" title="Dashboard" />
            <v-list-item :active="route().current('group.index')" :value="route('group.index')" prepend-icon="mdi-dots-circle" title="Abteilungen" />
            <v-list-item
                v-if="$page.props.auth.user.user_administration"
                :active="route().current('user.index')"
                :value="route('user.index')"
                prepend-icon="mdi-account-group"
                title="Mitarbeiter"
            />
            <v-list-item
                :active="route().current('workLog.index')"
                :value="route('workLog.index')"
                prepend-icon="mdi-clock-outline"
                title="Arbeitszeiten"
            />
            <v-list-item
                :active="route().current('operatingSite.index')"
                :value="route('operatingSite.index')"
                prepend-icon="mdi-map-marker"
                title="Betriebsorte"
            />
            <v-list-item
                :active="route().current('organization.show')"
                :value="route('organization.show', { organization: $page.props.organization.id })"
                prepend-icon="mdi-domain"
                title="Organisation"
            />
        </template>
    </v-list>
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
