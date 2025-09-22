<script setup lang="ts">
import TicketCreateDialog from './TicketCreateDialog.vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import TicketShowDialog from './TicketShowDialog.vue';
import RecordCreateDialog from './RecordCreateDialog.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import { PRIORITIES, TicketRecord } from '@/types/types';
import { ref } from 'vue';
import { DateTime } from 'luxon';

defineProps<{
    tickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    tab: 'archive' | 'finishedTickets' | 'newTickets';
}>();

const form = useForm({});

const search = ref('');

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
                { title: 'Titel', key: 'title' },
                { title: 'Kunde', key: 'customer.name' },
                { title: 'Priorität', key: 'priorityText' },
                { title: 'Erstellt von', key: 'user.name' },
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
                    };
                }).filter(t => {
                    const searchString = search.replace(/\W/gi,'').toLowerCase();
                    const ticketValue = (t.title+t.customer.name+t.user.name+t.assigneeName+t.priorityText).toLowerCase().replace(/\W/gi,'')
                    return ticketValue.includes(searchString)
                })
            "
            hover
        >
            <template v-slot:header.actions>
                <div class="d-flex ga-2 align-center">
                    <v-text-field v-model="search" placeholder="Suche" variant="outlined" density="compact" hide-details></v-text-field>
                    <TicketCreateDialog v-if="tab === 'newTickets'" :customers="customers" :users="users" />
                </div>
            </template>
            <template v-slot:item.priorityText="{ item }">
                {{ PRIORITIES.find(p => p.value === item.priority)?.title }}
                <v-icon
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
                <v-dialog v-if="tab === 'newTickets'" max-width="1000">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn title="Auftrag abschließen" v-bind="activatorProps" variant="text" icon="mdi-check" />
                    </template>
                    <template v-slot:default="{ isActive }">
                        <v-card :title="'Auftrag als abgeschlossen markieren'">
                            <template #append>
                                <v-btn icon variant="text" @click.stop="isActive.value = false">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </template>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="12">
                                        Bist du dir sicher, dass du diesen Auftrag als abgeschlossen markieren möchtest?
                                        <br />
                                        Du kannst danach keine weiteren Einträge für diesen Auftrag hinzufügen oder bearbeiten.
                                    </v-col>
                                    <v-col cols="12" class="text-end">
                                        <v-btn
                                            color="primary"
                                            @click.stop="
                                                form.patch(route('ticket.finish', { ticket: item.id }), {
                                                    onSuccess: () => (isActive.value = false),
                                                })
                                            "
                                            :loading="form.processing"
                                        >
                                            Bestätigen
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </template>
                </v-dialog>
                <RecordCreateDialog v-if="tab === 'newTickets'" :ticket="item" :users="users" mode="create" />
                <TicketShowDialog :ticket="item" :customers="customers" :users="users" :tab />
                <ConfirmDelete
                    v-if="tab === 'newTickets'"
                    content="Möchtest du dieses Ticket löschen?"
                    title="Löschen"
                    :route="route('ticket.destroy', { ticket: item.id })"
                ></ConfirmDelete>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
