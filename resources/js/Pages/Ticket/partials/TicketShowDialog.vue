<script setup lang="ts">
import { PRIORITIES, User } from '@/types/types';
import { formatDuration } from '@/utils';
import { DateTime } from 'luxon';
import { computed, ref, watchEffect } from 'vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import RecordCreateDialog from './RecordCreateDialog.vue';

const props = defineProps<{
    ticket: TicketProp;
    customers: CustomerProp[];
    users: UserProp[];
    tab: 'archive' | 'finishedTickets' | 'newTickets';
}>();

const form = useForm({
    priority: props.ticket.priority,
    assignees: [] as User['id'][],
    title: props.ticket.title,
    description: props.ticket.description,
    selected: props.ticket.records.filter(tr => tr.accounted_at).map(r => r.id),
});

watchEffect(() => {
    form.defaults({
        priority: props.ticket.priority,
        assignees: props.ticket.assignees.map(a => a.id) ?? [],
        title: props.ticket.title,
        description: props.ticket.description,
        selected: props.ticket.records.filter(tr => tr.accounted_at).map(r => r.id),
    });
    form.reset();
});

const showDialog = ref(Number(route().params['openTicket']) == props.ticket.id);

const hasRecords = computed(() => props.ticket.records.length > 0);

const durationSum = computed(() => props.ticket.records.reduce((a, c) => a + c.duration, 0));

const selectedDurationSum = computed(() =>
    props.ticket.records.filter(tr => form.selected.find(s => s === tr.id)).reduce((a, c) => a + c.duration, 0),
);
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
                            <v-col cols="12">
                                <v-autocomplete
                                    label="Zuweisung"
                                    :disabled="tab !== 'newTickets'"
                                    required
                                    multiple
                                    chips
                                    :items="
                                        users.map(u => ({
                                            value: u.id,
                                            title: `${u.first_name} ${u.last_name}`,
                                            props: { subtitle: u.job_role ?? '' },
                                        }))
                                    "
                                    :error-messages="form.errors.assignees"
                                    v-model="form.assignees"
                                ></v-autocomplete>
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
                                    :show-select="tab !== 'archive'"
                                >
                                    <template v-slot:item.start="{ item }">
                                        {{ DateTime.fromSQL(item.start).toFormat('dd.MM.yyyy HH:mm') }}
                                    </template>

                                    <template v-slot:item.duration="{ item }">
                                        {{ formatDuration(item.duration, 'minutes', 'duration') }}
                                    </template>

                                    <template v-slot:item.actions="{ item }">
                                        <RecordCreateDialog :ticket="ticket" :users="users" :record="item" mode="update"></RecordCreateDialog>
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
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-surface-medium">
                                                        <tr>
                                                            <th>Beschreibung</th>
                                                            <td class="py-2">{{ item.description }}</td>
                                                        </tr>
                                                    </tbody>
                                                </v-table>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-slot:bottom v-if="tab !== 'archive'">
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
                                <v-btn color="primary" type="submit" :loading="form.processing" :disabled="!form.isDirty">
                                    {{ tab === 'newTickets' ? 'Änderungen und Abrechnungen speichern' : 'Abrechnungen speichern' }}
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-form>
        </template>
    </v-dialog>
</template>
