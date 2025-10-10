<script setup lang="ts">
import { ref } from 'vue';
import { CustomerProp, Tab, TicketProp, UserProp } from './ticketTypes';
import TicketTable from './TicketTable.vue';

const props = defineProps<{
    tickets: TicketProp[];
    archiveTickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
}>();
const tab = ref<Tab>(
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
            Offene Aufträge
            <v-chip class="ms-2">
                {{ tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length == 0).length }}
            </v-chip>
        </v-tab>
        <v-tab value="workingTickets">
            Aufträge in Bearbeitung
            <v-chip class="ms-2">
                {{ tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length > 0).length }}
            </v-chip>
        </v-tab>
        <v-tab value="finishedTickets">
            Abgeschlossene Aufträge
            <v-chip class="ms-2">{{ tickets.filter(t => t.finished_at !== null).length }}</v-chip>
        </v-tab>
        <v-tab value="archive">Archiv</v-tab>
    </v-tabs>
    <v-tabs-window v-model="tab">
        <v-tabs-window-item value="newTickets">
            <TicketTable
                :tickets="tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length == 0)"
                :customers="customers"
                :users="users"
                tab="newTickets"
            />
        </v-tabs-window-item>
        <v-tabs-window-item value="workingTickets">
            <TicketTable
                :tickets="tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length > 0)"
                :customers="customers"
                :users="users"
                tab="newTickets"
            />
        </v-tabs-window-item>
        <v-tabs-window-item value="finishedTickets">
            <TicketTable :tickets="tickets.filter(t => t.finished_at !== null)" :customers="customers" :users="users" tab="finishedTickets" />
        </v-tabs-window-item>
        <v-tabs-window-item value="archive">
            <TicketTable :tickets="archiveTickets" :customers="customers" :users="users" tab="archive" />
        </v-tabs-window-item>
    </v-tabs-window>
</template>
