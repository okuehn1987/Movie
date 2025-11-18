<script setup lang="ts">
import { ref, watch } from 'vue';
import { CustomerProp, OperatingSiteProp, Tab, TicketProp, UserProp } from './ticketTypes';
import TicketTable from './TicketTable.vue';
import { Paginator } from '@/types/types';
import TicketArchiveTable from './TicketArchiveTable.vue';

const props = defineProps<{
    tickets: TicketProp[];
    archiveTickets: Paginator<TicketProp>;
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
    tab: Tab;
}>();

watch(
    () => props.tab,
    () => {
        currentTab.value = props.tab;
    },
);

const currentTab = ref(props.tab);
</script>
<template>
    <v-tabs v-model="currentTab">
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
    <v-tabs-window v-model="currentTab">
        <v-tabs-window-item value="newTickets">
            <TicketTable
                :tickets="tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length == 0)"
                :customers="customers"
                :users="users"
                :operatingSites
                tab="newTickets"
            />
        </v-tabs-window-item>
        <v-tabs-window-item value="workingTickets">
            <TicketTable
                :tickets="tickets.filter(t => t.finished_at === null && t.assignees.filter(a => a.pivot.status == 'accepted').length > 0)"
                :customers="customers"
                :users="users"
                :operatingSites
                tab="workingTickets"
            />
        </v-tabs-window-item>
        <v-tabs-window-item value="finishedTickets">
            <TicketTable
                :tickets="tickets.filter(t => t.finished_at !== null)"
                :customers="customers"
                :users="users"
                :operatingSites
                tab="finishedTickets"
            />
        </v-tabs-window-item>
        <v-tabs-window-item value="archive">
            <TicketArchiveTable :archiveTickets="archiveTickets" :customers="customers" :users="users" :operatingSites />
        </v-tabs-window-item>
    </v-tabs-window>
</template>
