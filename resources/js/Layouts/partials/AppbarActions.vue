<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDisplay } from 'vuetify';
import ReportBugDialog from './ReportBugDialog.vue';

const display = useDisplay();
</script>

<template>
    <v-btn color="primary" stacked @click.stop="router.get(route('notification.index'))">
        <v-badge
            v-if="$page.props.unreadNotifications.length > 0"
            :content="$page.props.unreadNotifications.length <= 99 ? $page.props.unreadNotifications.length : '99+'"
            color="error"
        >
            <v-icon icon="mdi-bell"></v-icon>
        </v-badge>
        <v-icon v-else icon="mdi-bell"></v-icon>
    </v-btn>
    <v-menu :location="display.smAndDown.value ? 'bottom left' : 'bottom'">
        <template #activator="{ props: activatorProps }">
            <v-btn stacked color="primary" prepend-icon="mdi-account-details" v-bind="activatorProps"></v-btn>
        </template>
        <v-list min-width="200px">
            <v-list-item @click.stop="router.get(route('user.profile', { user: $page.props.auth.user.id }))">
                <v-icon icon="mdi-account" color="primary" class="me-2"></v-icon>
                Profil
            </v-list-item>
            <v-list-item @click.stop="() => {}">
                <ReportBugDialog></ReportBugDialog>
            </v-list-item>
            <v-list-item @click.stop="router.post(route('logout'))">
                <v-icon icon="mdi-logout" color="primary" class="me-2"></v-icon>
                Abmelden
            </v-list-item>
        </v-list>
    </v-menu>
</template>
<style scoped>
#notification-menu :deep(.v-overlay__content) {
    left: unset !important;
    right: 0 !important;
}
</style>
