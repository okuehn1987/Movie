<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import { Canable, PRIORITIES } from '@/types/types';
import { formatDuration } from '@/utils';
import { DateTime } from 'luxon';
import { computed, ref, watch } from 'vue';
import RecordCreateDialog from './RecordCreateDialog.vue';
import { CustomerProp, OperatingSiteProp, Tab, TicketProp, UserProp } from './ticketTypes';

const props = defineProps<{
    ticket: TicketProp & Canable;
    customers: CustomerProp[];
    users: UserProp[];
    tab: Tab;
    operatingSites: OperatingSiteProp[];
}>();

const form = useForm({
    priority: props.ticket.priority,
    assignees: props.ticket.assignees.map(a => a.id),
    title: props.ticket.title,
    description: props.ticket.description,
    selected: props.ticket.records.filter(tr => tr.accounted_at).map(r => r.id),
});

watch(
    props.ticket,
    () => {
        form.defaults({
            priority: props.ticket.priority,
            assignees: props.ticket.assignees.map(a => a.id) ?? [],
            title: props.ticket.title,
            description: props.ticket.description,
            selected: props.ticket.records.filter(tr => tr.accounted_at).map(r => r.id),
        }).reset();
    },
    { deep: true },
);

const showDialog = ref(Number(route().params['openTicket']) == props.ticket.id);

const hasRecords = computed(() => props.ticket.records.length > 0);

const durationSum = computed(() => props.ticket.records.reduce((a, c) => a + c.duration, 0));

const selectedDurationSum = computed(() =>
    props.ticket.records.filter(tr => form.selected.find(s => s === tr.id)).reduce((a, c) => a + c.duration, 0),
);

const statusIcon = {
    accepted: 'mdi-check',
    created: 'mdi-timer-sand',
    declined: 'mdi-close',
} as const;
</script>
<template>
    <v-dialog max-width="1000px" height="800px" v-model="showDialog">
        <template #activator="{ props: activatorProps }">
            <v-btn title="Auftrag anzeigen" v-bind="activatorProps" icon variant="text">
                <v-icon icon="mdi-eye"></v-icon>
            </v-btn>
        </template>
        <template #default="{ isActive }">
            <v-form
                @submit.prevent="
                    form.patch(route('ticket.update', { ticket: ticket.id }), {
                        onSuccess: () => {
                            form.reset();
                            isActive.value = false;
                        },
                    })
                "
            >
                <v-card title="Auftrag anzeigen">
                    <template #append>
                        <v-btn icon variant="text" @click="isActive.value = false">
                            <v-icon>mdi-close</v-icon>
                        </v-btn>
                    </template>
                    <v-divider></v-divider>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" md="8">
                                <v-text-field label="Kunde" :model-value="ticket.customer.name" disabled></v-text-field>
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-select
                                    :items="PRIORITIES"
                                    label="Priorität"
                                    required
                                    :error-messages="form.errors.priority"
                                    v-model="form.priority"
                                    :disabled="tab !== 'newTickets'"
                                >
                                    <template v-slot:selection="{ item }">
                                        {{ item.raw.title }}
                                        <v-icon class="ms-2" :icon="item.raw.icon" :color="item.raw.color"></v-icon>
                                    </template>
                                    <template v-slot:item="{ props: itemProps, item }">
                                        <v-list-item v-bind="itemProps">
                                            <template #append>
                                                <v-icon :icon="item.raw.icon" :color="item.raw.color"></v-icon>
                                            </template>
                                        </v-list-item>
                                    </template>
                                </v-select>
                            </v-col>
                            <v-col cols="12"></v-col>
                            <v-col cols="12" md="12">
                                <v-autocomplete
                                    label="Zuweisung"
                                    :disabled="tab !== 'newTickets' && tab !== 'workingTickets'"
                                    required
                                    multiple
                                    chips
                                    v-model="form.assignees"
                                    :error-messages="form.errors.assignees"
                                    :items="
                                        users.map(u => ({
                                            value: u.id,
                                            title: `${u.first_name} ${u.last_name}`,
                                            props: { subtitle: u.job_role ?? '' },
                                        }))
                                    "
                                >
                                    <template v-slot:chip="{ item }">
                                        <v-chip
                                            :prepend-icon="statusIcon[ticket.assignees.find(u => u.id === item.value)?.pivot.status ?? 'created']"
                                        ></v-chip>
                                    </template>
                                    <template #append>
                                        <v-tooltip>
                                            <template v-slot:activator="{ props }">
                                                <v-icon v-bind="props" icon="mdi-information-outline" variant="text" color="primary"></v-icon>
                                            </template>
                                            <v-icon>mdi-check</v-icon>
                                            : angenommen
                                            <br />
                                            <v-icon>mdi-timer-sand</v-icon>
                                            : zugewiesen
                                            <br />
                                            <v-icon>mdi-close</v-icon>
                                            : abgelehnt
                                        </v-tooltip>
                                    </template>
                                </v-autocomplete>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Betreff"
                                    v-model="form.title"
                                    :disabled="tab !== 'newTickets'"
                                    :error-messages="form.errors.title"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field
                                    label="Beschreibung"
                                    v-model="form.description"
                                    :disabled="tab !== 'newTickets'"
                                    :error-messages="form.errors.description"
                                ></v-text-field>
                            </v-col>
                            <template v-if="hasRecords">
                                <v-data-table-virtual
                                    v-model="form.selected"
                                    :headers="[
                                        { title: 'Start', key: 'start' },
                                        { title: 'Auftragsdauer', key: 'duration' },
                                        { title: 'Erstellt von', key: 'userName' },
                                        { title: '', key: 'actions', width: '1px', sortable: false },
                                    ]"
                                    :items="
                                        ticket.records.map(r => ({
                                            ...r,
                                            userName: r.user.first_name + ' ' + r.user.last_name,
                                        }))
                                    "
                                    show-expand
                                    :show-select="tab !== 'archive' && can('ticket', 'account', ticket)"
                                >
                                    <template v-slot:item.start="{ item }">
                                        {{ DateTime.fromSQL(item.start).toFormat('dd.MM.yyyy HH:mm') }}
                                    </template>

                                    <template v-slot:item.duration="{ item }">
                                        {{ formatDuration(item.duration, 'minutes', 'duration') }}
                                    </template>

                                    <template v-slot:item.actions="{ item }">
                                        <RecordCreateDialog
                                            v-if="tab != 'finishedTickets'"
                                            :ticket="ticket"
                                            :users="users"
                                            :record="item"
                                            :operatingSites
                                        ></RecordCreateDialog>
                                    </template>
                                    <template v-slot:item.data-table-expand="{ internalItem, isExpanded, toggleExpand }">
                                        <v-btn size="small" variant="text" border @click="toggleExpand(internalItem)">
                                            <v-icon :icon="isExpanded(internalItem) ? 'mdi-chevron-up' : 'mdi-chevron-down'"></v-icon>
                                        </v-btn>
                                    </template>
                                    <template v-slot:expanded-row="{ columns, item }">
                                        <tr>
                                            <td :colspan="columns.length" class="py-2">
                                                <v-table density="compact">
                                                    <thead class="bg-surface-light">
                                                        <tr>
                                                            <th>Ressourcen</th>
                                                            <td class="py-2">{{ item.resources }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-surface-medium">
                                                        <tr>
                                                            <th>Beschreibung</th>
                                                            <td class="py-2">{{ item.description }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr v-for="(file, index) in item.files" :key="index">
                                                            <th v-if="index == 0">Anhang</th>
                                                            <th v-else></th>
                                                            <td class="py-2">{{ file.original_name }}</td>
                                                            <td style="width: 1px; padding: 0">
                                                                <v-btn
                                                                    variant="text"
                                                                    :href="route('ticketRecordFile.download', { ticketRecordFile: file.id })"
                                                                    target="_blank"
                                                                    color="primary"
                                                                    icon="mdi-download"
                                                                ></v-btn>
                                                            </td>
                                                            <td style="width: 1px; padding: 0">
                                                                <ConfirmDelete
                                                                    title="Datei löschen"
                                                                    content="Bist du dir sicher, dass du diese Datei löschen möchtest?"
                                                                    :route="route('ticketRecordFile.deleteFile', { ticketRecordFile: file.id })"
                                                                ></ConfirmDelete>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </v-table>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-slot:bottom v-if="tab !== 'archive' && can('ticket', 'account', ticket)">
                                        <v-row class="text-end" no-gutters>
                                            <template v-if="true">
                                                <v-col cols="12" md="11">Ausgewählte Auftragsdauer</v-col>
                                                <v-col cols="12" md="1">{{ formatDuration(selectedDurationSum, 'minutes', 'duration') }}</v-col>
                                            </template>
                                            <v-col cols="12" md="11">Gesamte Auftragsdauer</v-col>
                                            <v-col cols="12" md="1">{{ formatDuration(durationSum, 'minutes', 'duration') }}</v-col>
                                        </v-row>
                                    </template>
                                </v-data-table-virtual>
                            </template>
                            <v-col v-if="tab !== 'archive'" cols="12" class="text-end">
                                <v-btn
                                    v-if="can('ticket', 'account', ticket)"
                                    color="primary"
                                    type="submit"
                                    :loading="form.processing"
                                    :disabled="!form.isDirty"
                                >
                                    {{ tab === 'newTickets' ? 'Änderungen und Abrechnungen speichern' : 'Abrechnungen speichern' }}
                                </v-btn>
                                <v-btn
                                    v-else-if="tab === 'newTickets'"
                                    color="primary"
                                    type="submit"
                                    :loading="form.processing"
                                    :disabled="!form.isDirty"
                                >
                                    Änderungen speichern
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-form>
        </template>
    </v-dialog>
</template>
