<script setup lang="ts">
import TicketCreateDialog from './TicketCreateDialog.vue';
import { CustomerProp, TicketProp, UserProp } from './ticketTypes';
import TicketShowDialog from './TicketShowDialog.vue';
import RecordCreateDialog from './RecordCreateDialog.vue';
import ConfirmDelete from '@/Components/ConfirmDelete.vue';

defineProps<{
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
                { title: 'Zugewiesen an', key: 'assigneeName' },
                { title: '', key: 'actions', align: 'end' },
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
                        assigneeName: assignee
                    };
                })
            "
            hover
        >
            <template v-slot:header.actions>
                <TicketCreateDialog v-if="tab === 'newTickets'" :customers="customers" :users="users" />
            </template>
            <template v-slot:item.priority="{ item }">
                {{ item.priority }}
            </template>
            <template v-slot:item.assigneeName="{ item }">
                {{ item.assigneeName }}
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
                <RecordCreateDialog v-if="tab === 'newTickets'" :ticket="item" :users="users" />
                <TicketShowDialog :ticket="item" :customers="customers" :users="users" :tab />
                <ConfirmDelete
                    content="Möchtest du dieses Ticket löschen?"
                    title="Löschen"
                    :route="route('ticket.destroy', { ticket: item.id })"
                ></ConfirmDelete>
            </template>
        </v-data-table-virtual>
    </v-card>
</template>
