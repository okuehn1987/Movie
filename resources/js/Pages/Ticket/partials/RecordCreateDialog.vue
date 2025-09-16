<script setup lang="ts">
import { TicketProp, UserProp } from './ticketTypes';

defineProps<{
    users: UserProp[];
    ticket: TicketProp;
}>();

const recordForm = useForm({
    start: null as string | null,
    duration: null as number | null,
    description: '',
    resources: '',
});
</script>
<template>
    <v-dialog max-width="600px">
        <template #activator="{ props: activatorProps }">
            <v-btn title="Eintrag hinzufügen" v-bind="activatorProps" variant="text"><v-icon>mdi-plus</v-icon></v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card title="Eintrag erstellen">
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            recordForm.post(route('ticket.record.store', { ticket: ticket.id }), {
                                onSuccess: () => {
                                    recordForm.reset();
                                    isActive.value = false;
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Start"
                                    type="datetime-local"
                                    :model-value="recordForm.start"
                                    @update:model-value="val => (recordForm.start = val)"
                                    :error-messages="recordForm.errors.start"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Dauer (in Stunden)"
                                    type="time"
                                    v-model="recordForm.duration"
                                    :error-messages="recordForm.errors.duration"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    label="Beschreibung"
                                    v-model="recordForm.description"
                                    :error-messages="recordForm.errors.description"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    label="Verwendete Ressourcen"
                                    rows="1"
                                    v-model="recordForm.resources"
                                    :error-messages="recordForm.errors.resources"
                                ></v-textarea>
                            </v-col>
                            <v-col class="text-end">
                                <v-btn color="primary" type="submit" :loading="recordForm.processing">Speichern</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
