<script setup lang="ts">
import { DateTimeString, PRIORITIES } from '@/types/types';
import TicketCreateDialog from './TicketCreateDialog.vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import TicketShowDialog from './TicketShowDialog.vue';
import RecordCreateDialog from './RecordCreateDialog.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    tickets: TicketProp[];
    customers: CustomerProp[];
    users: UserProp[];
    tab: 'archive' | 'finishedTickets' | 'newTickets';
}>();

const form = useForm({});
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
                <TicketCreateDialog v-if="tab === 'newTickets'" :customers="customers" :users="users" />
            </template>
            <template v-slot:item.priority="{ item }">
                {{ PRIORITIES[item.priority] }}
            </template>
            <template v-slot:item.actions="{ item }">
                <v-dialog v-if="tab === 'newTickets'" max-width="1000">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" variant="text" icon="mdi-check" />
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
                                            variant="text"
                                            color="red"
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
                <RecordCreateDialog v-if="tab === 'newTickets'" :ticket="item" :users="users" />
                <TicketShowDialog :ticket="item" :tab />
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
