<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Ticket } from '@/types/types';

defineProps<{
    tickets: Ticket[];
}>();
</script>
<template>
    <AdminLayout title="Tickets">
        <v-card>
            <v-data-table
                fixed-header
                :headers="[
                    { title: 'Titel', key: 'title' },
                    { title: 'Kunde', key: 'customer.name' },
                    { title: 'Priorität', key: 'priority' },
                    { title: 'Status', key: 'status' },
                    { title: 'Erstellt von', key: 'user.first_name' },
                    { title: 'Zugewiesen an', key: 'assignee.first_name' },
                    { title: 'Zugewiesen am', key: 'assigned_at' },
                    { title: '', key: 'actions', align: 'end' },
                ]"
                :items="
                    tickets.map(t => ({
                        ...t,
                        assigned_at: t.assigned_at ? new Date(t.assigned_at).toLocaleDateString('de-DE') : '',
                    }))
                "
                hover
            >
                <template v-slot:header.actions>
                    <v-btn color="primary">
                        <v-icon icon="mdi-plus"></v-icon>
                    </v-btn>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon>
                        <v-icon icon="mdi-eye"></v-icon>
                    </v-btn>
                </template>
            </v-data-table>
        </v-card>
    </AdminLayout>
</template>
