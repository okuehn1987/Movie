<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import TicketTable from './partials/TicketTable.vue';
import { CustomerProp, TicketProp, UserProp } from './partials/ticketTypes';

defineProps<{
    tickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
}>();

const tab = ref('newTickets');
</script>
<template>
    <AdminLayout title="Tickets">
        <v-tabs v-model="tab">
            <v-tab value="newTickets">unbearbeitete Aufträge</v-tab>
            <v-tab value="archive">Archiv</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item value="newTickets">
                <TicketTable :tickets="tickets" :customers="customers" :users="users" />
            </v-tabs-window-item>
            <v-tabs-window-item value="archive">
                <TicketTable :tickets="tickets.filter(t => t.status == 'accepted')" :customers="customers" :users="users" />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
