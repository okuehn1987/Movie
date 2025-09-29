<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { defineProps } from 'vue';
defineProps<{
    title: string;
    content: string;
    route: string;
}>();
</script>

<template>
    <v-dialog max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" color="error" variant="text" icon="mdi-delete" />
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="title">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row>
                        <v-col cols="12">{{ content }}</v-col>
                        <v-col cols="12" class="text-end">
                            <v-btn @click.stop="router.delete(route, { onSuccess: () => (isActive.value = false) })" color="error">Löschen</v-btn>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
