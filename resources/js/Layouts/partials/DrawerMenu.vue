<script setup lang="ts">
import { router } from '@inertiajs/vue3';
</script>

<template>
    <v-list
        nav
        @click:select="route => router.get(route.id as string)"
        :items="[
            { props: { active: route().current('dashboard'), prependIcon: 'mdi-view-dashboard' }, value: route('dashboard'), title: 'Dashboard' },
        ]"
    />
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
