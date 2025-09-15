<script setup lang="ts">
import { PRIORITIES, TicketRecord } from '@/types/types';
import { formatDuration } from '@/utils';
import { DateTime } from 'luxon';
import { computed } from 'vue';
import { TicketProp } from './ticketTypes';

const props = defineProps<{
    ticket: TicketProp;
}>();

const form = useForm({
    priority: props.ticket.priority,
    assigneeName: props.ticket.assignee?.first_name + ' ' + props.ticket.assignee?.last_name,
    title: props.ticket.title,
    description: props.ticket.description,
    selected: [] as TicketRecord['id'][],
});

const hasRecords = computed(() => props.ticket.records.length > 0);

const durationSum = computed(() => props.ticket.records.reduce((a, c) => a + c.duration, 0));

const selectedDurationSum = computed(() =>
    props.ticket.records.filter(tr => form.selected.find(s => s === tr.id)).reduce((a, c) => a + c.duration, 0),
);
</script>
<template>
    <v-dialog max-width="1000px" height="800px">
        <template #activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" icon variant="text">
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
                            <v-col cols="12" md="6"><v-text-field label="Kunde" :model-value="ticket.customer.name" readonly></v-text-field></v-col>
                            <v-col cols="12" md="6">
                                <v-text-field label="Priorität" v-model="PRIORITIES[form.priority]" readonly></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <!-- TODO: Zuweisung wird multi select mit chips wenn die relation vorhanden ist -->
                                <v-text-field label="Zuweisung" readonly v-model="form.assigneeName"></v-text-field>
                            </v-col>
                            <v-col cols="12"><v-text-field label="Betreff" v-model="form.title"></v-text-field></v-col>
                            <v-col cols="12"><v-text-field label="Beschreibung" v-model="form.description"></v-text-field></v-col>
                            <template v-if="hasRecords">
                                <v-data-table-virtual
                                    v-model="form.selected"
                                    :headers="[
                                        { title: 'Start', key: 'start' },
                                        { title: 'Auftragsdauer', key: 'duration' },
                                        { title: 'Erstellt von', key: 'userName' },
                                    ]"
                                    :items="
                                        ticket.records.map(r => ({
                                            ...r,
                                            duration: formatDuration(r.duration, 'minutes', 'duration'),
                                            start: DateTime.fromSQL(r.start).toFormat('dd.MM.yyyy HH:mm'),
                                            userName: r.user.first_name + ' ' + r.user.last_name,
                                        }))
                                    "
                                    show-expand
                                    show-select
                                >
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
                                    <template v-slot:bottom>
                                        <v-row class="text-end" no-gutters>
                                            <!-- TODO: if Aufträge ausgewählt -->
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
                            <v-col cols="12" class="text-end">
                                <v-btn color="primary" type="submit" :loading="form.processing">Änderungen und Abrechnungen speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-form>
        </template>
    </v-dialog>
</template>
