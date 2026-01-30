<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDisplay } from 'vuetify';

const display = useDisplay();
</script>

<template>
    <v-menu :location="display.smAndDown.value ? 'bottom left' : 'bottom'">
        <template #activator="{ props: activatorProps }">
            <v-btn stacked color="primary" prepend-icon="mdi-account-details" v-bind="activatorProps"></v-btn>
        </template>
        <v-list min-width="200px">
            <v-list-item @click.stop="router.get(route('user.profile', { user: $page.props.auth.user.id }))">
                <v-icon icon="mdi-account" color="primary" class="me-2"></v-icon>
                Profil
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
