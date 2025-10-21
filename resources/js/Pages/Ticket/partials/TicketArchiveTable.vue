<script setup lang="ts">
import { Paginator, PRIORITIES, TicketRecord } from '@/types/types';
import { usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';
import TicketArchiveFilter from './TicketArchiveFilter.vue';
import TicketShowDialog from './TicketShowDialog.vue';
import { CustomerProp, OperatingSiteProp, TicketProp, UserProp } from './ticketTypes';

const props = defineProps<{
    archiveTickets: Paginator<TicketProp>;
    customers: CustomerProp[];
    users: UserProp[];
    operatingSites: OperatingSiteProp[];
}>();

const { currentPage, lastPage, data, itemsPerPage } = usePagination(toRefs(props), 'archiveTickets', { tab: 'archive' });

// const search = ref('');

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
            v-model:page="currentPage"
            :itemsPerPage
            :headers="[
                { title: 'Ticket', key: 'reference_number', width: '1px' },
                { title: 'Datum', key: 'created_at', width: '1px' },
                { title: 'Titel', key: 'title' },
                { title: 'Kunde', key: 'customer.name' },
                { title: 'Termin', key: 'appointment_at' },
                { title: 'Zugewiesen an', key: 'assigneeName' },
                { title: 'Abgerechnet am', key: 'accounted_at' },
                { title: '', key: 'actions', align: 'end', width: '250px', sortable: false },
            ]"
            :items="
                data.map(t => {
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
                })
            "
            hover
        >
            <template v-slot:header.actions>
                <TicketArchiveFilter :customers :users :operating-sites></TicketArchiveFilter>
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
                <TicketShowDialog :ticket="item" :customers="customers" :users="users" tab="archive" :operatingSites />
            </template>
            <template v-slot:bottom>
                <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
