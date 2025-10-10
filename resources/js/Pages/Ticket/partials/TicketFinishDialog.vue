<script setup lang="ts">
import { Ticket } from '@/types/types';

defineProps<{
    tab: 'archive' | 'finishedTickets' | 'newTickets';
    item: Pick<Ticket, 'id'>;
}>();

const form = useForm({});
</script>
<template>
    <v-dialog v-if="tab !== 'archive'" max-width="1000">
        <template v-slot:activator="{ props: activatorProps }">
            <v-btn title="Auftrag abschließen" v-bind="activatorProps" variant="text" icon="mdi-check" />
        </template>
        <template v-slot:default="{ isActive }">
            <v-card :title="'Auftrag als  ' + (tab == 'finishedTickets' ? 'unbearbeitet' : 'abgeschlossen') + ' markieren'">
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
                                {{ tab == 'finishedTickets' ? 'unbearbeitet' : 'abgeschlossen' }} markieren möchtest?
                                <br />
                                {{
                                    tab == 'finishedTickets'
                                        ? ''
                                        : 'Du kannst danach keine weiteren Einträge für diesen Auftrag hinzufügen oder bearbeiten.'
                                }}
                            </v-alert>
                        </v-col>
                        <v-col cols="12" class="text-end">
                            <v-btn
                                color="primary"
                                @click.stop="
                                    form.patch(route(tab == 'finishedTickets' ? 'ticket.unfinish' : 'ticket.finish', { ticket: item.id }), {
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
