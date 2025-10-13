<script setup lang="ts">
import TicketCreateDialog from './TicketCreateDialog.vue';
import { CustomerProp, OperatingSiteProp, TicketProp, UserProp } from './ticketTypes';
import TicketShowDialog from './TicketShowDialog.vue';
import RecordCreateDialog from './RecordCreateDialog.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import { PRIORITIES, TicketRecord } from '@/types/types';
import { ref } from 'vue';
import { DateTime } from 'luxon';
import TicketFinishDialog from './TicketFinishDialog.vue';

defineProps<{
    tickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    tab: 'archive' | 'finishedTickets' | 'newTickets' | 'workingTickets';
    operatingSites: OperatingSiteProp[];
}>();

const search = ref('');

const acceptTicketForm = useForm({});

function getAccountedAt(records: TicketRecord[]) {
    const sortedRecords = records.sort((a, b) => {
        if (!a.accounted_at) return 1;
        if (!b.accounted_at) return -1;
        return b.accounted_at.localeCompare(a.accounted_at);
    });
    if (!sortedRecords[0]) return '-';

    const date = DateTime.fromSQL(sortedRecords[0].accounted_at ?? '');

    if (date.isValid) return date.toFormat('dd.MM.yyyy HH:mm');
    return '-';
}
</script>
<template>
    <v-card>
        <v-data-table-virtual
            fixed-header
            :headers="[
                { title: 'Prio', key: 'priorityText', width: '1px' },
                { title: 'Ticket', key: 'reference_number', width: '1px' },
                { title: 'Datum', key: 'created_at', width: '1px' },
                { title: 'Titel', key: 'title' },
                { title: 'Kunde', key: 'customer.name' },
                { title: 'Termin', key: 'appointment_at' },
                { title: 'Zugewiesen an', key: 'assigneeName' },
                ...(tab === 'archive' ? [{ title: 'Abgerechnet am', key: 'accounted_at' }] : []),
                { title: '', key: 'actions', align: 'end', sortable: false },
            ]"
            :items="
                tickets.map(t => {
                    const assignee = (()=>{
                        if(t.assignees.length == 0) return null;
                        const a = t.assignees[0]!
                        if(t.assignees.length == 1) return a.first_name + ' ' + a.last_name
                        return `${a.first_name} ${a.last_name} (+${t.assignees.length -1} weitere)`
                    })()
                    return {
                        ...t,
                        user: { ...t.user, name: t.user.first_name + ' ' + t.user.last_name },
                        assigneeName: assignee,
                        priorityText:  PRIORITIES.find(p => p.value === t.priority)?.title,
                        priorityValue: PRIORITIES.find(p=>p.value === t.priority)?.priorityValue,
                        assigneesNames: t.assignees.map(a => a.first_name + a.last_name).join(''),
                    };
                }).filter(t => {
                    const searchString = search.replace(/\W/gi,'').toLowerCase();
                    const ticketValue = (t.title+t.customer.name+t.user.name+t.assigneesNames+t.priorityText+t.reference_number).toLowerCase().replace(/\W/gi,'')
                    return ticketValue.includes(searchString)
                })
            "
            hover
        >
            <template v-slot:header.actions>
                <div class="d-flex ga-2 align-center">
                    <v-text-field v-model="search" placeholder="Suche" variant="outlined" density="compact" hide-details></v-text-field>
                    <TicketCreateDialog v-if="tab === 'newTickets'" :customers="customers" :users="users" :operatingSites />
                </div>
            </template>
            <template v-slot:item.created_at="{ item }">
                {{ DateTime.fromISO(item.created_at).toFormat('dd.MM.yyyy') }}
            </template>
            <template v-slot:item.appointment_at="{ item }">
                {{ item.appointment_at ? DateTime.fromSQL(item.appointment_at).toFormat("dd.MM. 'um' HH:mm 'Uhr'") : '-' }}
            </template>
            <template v-slot:item.priorityText="{ item }">
                <v-icon
                    :title="PRIORITIES.find(p => p.value === item.priority)?.title"
                    :icon="PRIORITIES.find(p => p.value === item.priority)?.icon"
                    :color="PRIORITIES.find(p => p.value === item.priority)?.color"
                ></v-icon>
            </template>
            <template v-slot:item.assigneeName="{ item }">
                {{ item.assigneeName }}
            </template>
            <template v-slot:item.accounted_at="{ item }">
                {{ getAccountedAt(item.records) }}
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn
                    v-if="
                        ['newTickets', 'workingTickets'].includes(tab) &&
                        !item.assignees.find(a => a.pivot.status === 'accepted' && a.pivot.user_id === $page.props.auth.user.id)
                    "
                    icon="mdi-handshake"
                    variant="text"
                    color="primary"
                    title="Ticket übernehmen"
                    @click.stop="acceptTicketForm.patch(route('ticket.accept', { ticket: item.id }))"
                ></v-btn>
                <TicketFinishDialog v-if="tab === 'workingTickets' || tab === 'finishedTickets'" :tab :item></TicketFinishDialog>
                <RecordCreateDialog v-if="tab === 'workingTickets'" :ticket="item" :users="users" :operatingSites />
                <TicketShowDialog :ticket="item" :customers="customers" :users="users" :tab />
                <ConfirmDelete
                    v-if="tab === 'newTickets' && can('ticket', 'delete', item)"
                    content="Möchtest du dieses Ticket löschen?"
                    title="Löschen"
                    :route="route('ticket.destroy', { ticket: item.id })"
                ></ConfirmDelete>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
