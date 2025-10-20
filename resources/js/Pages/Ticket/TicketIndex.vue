<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import TicketTable from './partials/TicketTable.vue';
import { CustomerProp, TicketProp, UserProp } from './partials/ticketTypes';

const props = defineProps<{
    tickets: TicketProp[];
    archiveTickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    tab: 'archive' | 'finishedTickets' | 'newTickets';
}>();

const tab = ref(
    props.archiveTickets.find(t => t.id === Number(route().params['openTicket']))
        ? 'archive'
        : props.tickets.find(t => t.finished_at !== null && t.id === Number(route().params['openTicket']))
        ? 'finishedTickets'
        : 'newTickets',
);
</script>
<template>
    <AdminLayout title="Tickets">
        <v-tabs v-model="tab">
            <v-tab value="newTickets">unbearbeitete Aufträge</v-tab>
            <v-tab value="finishedTickets">abgeschlossene Aufträge</v-tab>
            <v-tab value="archive">Archiv</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item value="newTickets">
                <TicketTable :tickets="tickets.filter(t => t.finished_at === null)" :customers="customers" :users="users" tab="newTickets" />
            </v-tabs-window-item>
            <v-tabs-window-item value="finishedTickets">
                <TicketTable :tickets="tickets.filter(t => t.finished_at !== null)" :customers="customers" :users="users" tab="finishedTickets" />
            </v-tabs-window-item>
            <v-tabs-window-item value="archive">
                <TicketTable :tickets="archiveTickets" :customers="customers" :users="users" tab="archive" />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
