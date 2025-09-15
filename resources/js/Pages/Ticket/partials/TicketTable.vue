<script setup lang="ts">
import { DateTimeString, PRIORITIES } from '@/types/types';
import TicketCreateDialog from './TicketCreateDialog.vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import TicketShowDialog from './TicketShowDialog.vue';
import RecordCreateDialog from './RecordCreateDialog.vue';

defineProps<{
    tickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
}>();
</script>
<template>
    <v-card>
        <v-data-table-virtual
            fixed-header
            :headers="[
                { title: 'Titel', key: 'title' },
                { title: 'Kunde', key: 'customer.name' },
                { title: 'Priorität', key: 'priority' },
                { title: 'Erstellt von', key: 'user.name' },
                { title: 'Zugewiesen an', key: 'assignee.name' },
                { title: 'Zugewiesen am', key: 'assigned_at' },
                { title: '', key: 'actions', align: 'end' },
            ]"
            :items="
                tickets.map(t => ({
                    ...t,
                    assigned_at: (t.assigned_at ? new Date(t.assigned_at).toLocaleDateString('de-DE') : '') as DateTimeString,
                    user: { ...t.user, name: t.user.first_name + ' ' + t.user.last_name },
                    assignee: t.assignee ? { ...t.assignee, name: t.assignee.first_name + ' ' + t.assignee.last_name } : null,
                }))
            "
            hover
        >
            <template v-slot:header.actions>
                <TicketCreateDialog :customers="customers" :users="users" />
            </template>
            <template v-slot:item.priority="{ item }">
                {{ PRIORITIES[item.priority] }}
            </template>
            <template v-slot:item.actions="{ item }">
                <RecordCreateDialog :ticket="item" :users="users" />
                <TicketShowDialog :ticket="item" />
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
