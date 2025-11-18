<script setup lang="ts">
import { Ticket } from '@/types/types';
import { Tab } from './ticketTypes';
import { computed } from 'vue';

const props = defineProps<{
    tab: Tab;
    item: Pick<Ticket, 'id'>;
}>();

const closedTicket = computed(() => ['finishedTickets', 'archive'].includes(props.tab));

const form = useForm({});
</script>
<template>
    <v-dialog max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn v-if="tab == 'workingTickets'" title="Auftrag abschließen" v-bind="activatorProps" variant="text" icon="mdi-check" />
            <v-btn v-else title="Auftrag weiterführen" v-bind="activatorProps" variant="text" icon="mdi-autorenew" />
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="'Auftrag als  ' + (closedTicket ? 'offen' : 'abgeschlossen') + ' markieren'">
                <template #append>
                    <v-btn icon variant="text" @click.stop="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-row>
                        <v-col cols="12">
                            <v-alert type="warning">
                                Bist du dir sicher, dass du diesen Auftrag als
                                {{ closedTicket ? 'offen' : 'abgeschlossen' }} markieren möchtest?
                                <br />
                                {{ closedTicket ? '' : 'Du kannst danach keine weiteren Einträge für diesen Auftrag hinzufügen oder bearbeiten.' }}
                            </v-alert>
                        </v-col>
                        <v-col cols="12" class="text-end">
                            <v-btn
                                color="primary"
                                @click.stop="
                                    form.patch(route(closedTicket ? 'ticket.unfinish' : 'ticket.finish', { ticket: item.id }), {
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
</template>
