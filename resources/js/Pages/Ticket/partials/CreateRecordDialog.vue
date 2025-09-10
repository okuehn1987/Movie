<script setup lang="ts">
import { CustomerProp, UserProp } from './ticketTypes';

defineProps<{
    customers: CustomerProp[];
    users: UserProp[];
}>();
</script>
<template>
    <v-dialog>
        <template #activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" variant="text"><v-icon>mdi-plus</v-icon></v-btn>
        </template>
    </v-dialog>
</template>
