<script setup lang="ts">
import { computed } from 'vue';
import { TicketProp } from './ticketTypes';
import { PRIORITIES } from '@/types/types';
import { DateTime } from 'luxon';
import { formatDuration } from '@/utils';

const props = defineProps<{
    ticket: TicketProp;
}>();

const form = useForm({
    priority: props.ticket.priority,
    assigneeName: props.ticket.assignee?.first_name + ' ' + props.ticket.assignee?.last_name,
    title: props.ticket.title,
    description: props.ticket.description,
});

const hasRecords = computed(() => props.ticket.records.length > 0);
</script>
<template>
    <v-dialog max-width="800px">
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
                                <v-data-table
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
                                    hide-default-footer
                                    show-expand
                                >
                                    <template v-slot:item.data-table-expand="{ internalItem, isExpanded, toggleExpand }">
                                        <v-btn
                                            :append-icon="isExpanded(internalItem) ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                                            :text="isExpanded(internalItem) ? 'Collapse' : 'More info'"
                                            class="text-none"
                                            color="medium-emphasis"
                                            size="small"
                                            variant="text"
                                            width="105"
                                            border
                                            slim
                                            @click="toggleExpand(internalItem)"
                                        ></v-btn>
                                    </template>
                                    <template v-slot:expanded-row="{ columns, item }">
                                        <tr>
                                            <td :colspan="columns.length" class="py-2">
                                                <v-table density="compact">
                                                    <tbody class="bg-surface-light">
                                                        <tr>
                                                            <th>Rating</th>
                                                            <th>Synopsis</th>
                                                            <th>Cast</th>
                                                        </tr>
                                                    </tbody>
                                                    <tbody>
                                                        <tr>
                                                            <td class="py-2">
                                                                <v-rating
                                                                    :model-value="item.start"
                                                                    color="orange-darken-2"
                                                                    density="comfortable"
                                                                    size="small"
                                                                    half-increments
                                                                    readonly
                                                                ></v-rating>
                                                            </td>
                                                            <td class="py-2">{{ item.duration }}</td>
                                                            <td class="py-2">{{ item.description }}</td>
                                                            <td class="py-2">{{ item.resources }}</td>
                                                        </tr>
                                                    </tbody>
                                                </v-table>
                                            </td>
                                        </tr>
                                    </template>
                                </v-data-table>
                            </template>
                            <v-col cols="12" class="text-end">
                                <v-btn color="primary" type="submit" :loading="form.processing">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-form>
        </template>
    </v-dialog>
</template>
