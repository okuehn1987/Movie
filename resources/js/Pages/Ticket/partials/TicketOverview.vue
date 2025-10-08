<script setup lang="ts">
import { ref } from 'vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import TicketTable from './TicketTable.vue';

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
    <v-tabs v-model="tab">
        <v-tab value="newTickets">
            unbearbeitete Aufträge
            <v-chip class="ms-2">{{ tickets.filter(t => t.finished_at === null).length }}</v-chip>
        </v-tab>
        <v-tab value="finishedTickets">
            abgeschlossene Aufträge
            <v-chip class="ms-2">{{ tickets.filter(t => t.finished_at !== null).length }}</v-chip>
        </v-tab>
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
</template>
