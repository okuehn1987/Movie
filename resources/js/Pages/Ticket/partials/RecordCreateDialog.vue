<script setup lang="ts">
import { DATETIME_LOCAL_FORMAT, TicketRecord } from '@/types/types';
import { TicketProp, UserProp } from './ticketTypes';
import { DateTime } from 'luxon';
import { secondsToDuration } from '@/utils';
import { ref } from 'vue';

const props = defineProps<{
    users: UserProp[];
    ticket: TicketProp;
    record?: TicketRecord;
    mode: 'create' | 'update';
}>();

const recordForm = useForm({
    start: props.record ? DateTime.fromSQL(props.record.start).toISO() : null,
    duration: secondsToDuration(props.record?.duration ?? 0),
    description: props.record?.description ?? '',
    resources: props.record?.resources ?? '',
});

const showDialog = ref(false);

function submit() {
    if (props.record) {
        recordForm.patch(route('record.update', { record: props.record.id }), {
            onSuccess: () => {
                recordForm.reset();
                showDialog.value = false;
            },
        });
    } else {
        recordForm.post(route('ticket.record.store', { ticket: props.ticket.id }), {
            onSuccess: () => {
                recordForm.reset();
                showDialog.value = false;
            },
        });
    }
}
</script>
<template>
    <v-dialog max-width="600px" v-model="showDialog">
        <template #activator="{ props: activatorProps }">
            <v-btn v-if="mode === 'create'" title="Eintrag hinzufügen" v-bind="activatorProps" variant="text"><v-icon>mdi-plus</v-icon></v-btn>
            <v-btn v-else title="Eintrag bearbeiten" v-bind="activatorProps" variant="text"><v-icon>mdi-pencil</v-icon></v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card :title="mode === 'create' ? 'Eintrag erstellen' : 'Eintrag bearbeiten'">
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text>
                    <!-- , (isActive.value = false), recordForm.reset() -->
                    <v-form @submit.prevent="submit">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    label="Start"
                                    type="datetime-local"
                                    :model-value="DateTime.fromISO(recordForm.start ?? '').toFormat(DATETIME_LOCAL_FORMAT)"
                                    @update:model-value="
                                        val => {
                                            recordForm.start = val;
                                        }
                                    "
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
                                <v-btn color="primary" type="submit" :loading="recordForm.processing" :disabled="!recordForm.isDirty">
                                    {{ mode === 'create' ? 'Speichern' : 'Änderungen speichern' }}
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
