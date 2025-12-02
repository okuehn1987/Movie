<script setup lang="ts">
import { Canable, DATETIME_LOCAL_FORMAT, Relations, TicketRecord, TicketRecordFile } from '@/types/types';
import { secondsToDuration } from '@/utils';
import { DateTime } from 'luxon';
import { computed, ref, watch } from 'vue';
import { OperatingSiteProp, TicketProp, UserProp } from './ticketTypes';

const props = defineProps<{
    users: UserProp[];
    ticket: TicketProp;
    record?: TicketRecord & Canable & { files: TicketRecordFile[]; user: Relations<'ticketRecord'>['user'] };
    operatingSites: OperatingSiteProp[];
}>();

const recordForm = useForm({
    start: props.record ? DateTime.fromSQL(props.record.start).toISO() : null,
    operatingSite: props.operatingSites.find(o => o.value.type === 'App\\Models\\OperatingSite')?.value,
    duration: secondsToDuration(props.record?.duration ?? 0),
    description: props.record?.description ?? '',
    resources: props.record?.resources ?? '',
    files: [] as File[],
});

const mode = computed(() => (props.record ? 'edit' : 'create'));

watch(
    () => props.record,
    () => {
        recordForm
            .defaults({
                start: props.record ? DateTime.fromSQL(props.record.start).toISO() : null,
                duration: secondsToDuration(props.record?.duration ?? 0),
                description: props.record?.description ?? '',
                resources: props.record?.resources ?? '',
                files: [],
            })
            .reset();
    },
);

const showDialog = ref(false);

function submit() {
    if (props.record) {
        recordForm
            .transform(data => ({ ...data, _method: 'patch' }))
            .post(route('ticketRecord.update', { ticketRecord: props.record.id }), {
                onSuccess: () => {
                    recordForm.reset();
                    showDialog.value = false;
                },
            });
    } else {
        recordForm.post(route('ticket.ticketRecord.store', { ticket: props.ticket.id }), {
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
            <v-btn v-if="record && can('ticketRecord', 'update', record)" title="Eintrag bearbeiten" v-bind="activatorProps" variant="text">
                <v-icon>mdi-pencil</v-icon>
            </v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card :title="mode === 'create' ? 'Eintrag erstellen' : 'Eintrag bearbeiten'">
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text style="max-height: 600px; overflow-y: auto">
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
                                <v-select
                                    label="Standort"
                                    v-model="recordForm.operatingSite"
                                    :items="operatingSites.filter(o => !('customer_id' in o) || o.customer_id === ticket.customer_id)"
                                    :error-messages="recordForm.errors.operatingSite"
                                ></v-select>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    label="Beschreibung"
                                    max-rows="10"
                                    rows="2"
                                    v-model="recordForm.description"
                                    :error-messages="recordForm.errors.description"
                                    auto-grow
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    label="Ressourcen"
                                    max-rows="10"
                                    rows="2"
                                    v-model="recordForm.resources"
                                    :error-messages="recordForm.errors.resources"
                                    auto-grow
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12">
                                <v-file-input
                                    label="Dateien anhängen"
                                    multiple
                                    v-model="recordForm.files"
                                    :error-messages="recordForm.errors['files.0']"
                                    accept="image/jpg, image/png, image/jpeg, image/avif, image/tiff, image/svg+xml, application/pdf"
                                ></v-file-input>
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
