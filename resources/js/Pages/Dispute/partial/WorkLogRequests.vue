<script setup lang="ts">
import { WorkLog } from '@/types/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { WorkLogProp } from './disputeTypes';

const props = defineProps<{
    workLogs: WorkLogProp[];
}>();

const workLogDialog = ref<WorkLogProp | null>(props.workLogs?.find(log => log.id == Number(route().params['openWorkLog'])) ?? null);
const showWorkLogDialog = ref(!!workLogDialog.value);

const currentPage = ref(1);

const changeStatusForm = useForm({
    accepted: false,
});

function openWorkLog(_: unknown, row: { item: { id: WorkLog['id'] } }) {
    const workLog = props.workLogs?.find(p => p.id === row.item.id);
    if (!workLog) return;
    workLogDialog.value = workLog;
    showWorkLogDialog.value = true;
}

function changeWorkLogStatus() {
    if (!workLogDialog.value) return;
    changeStatusForm.patch(route('workLog.update', { workLog: workLogDialog.value.id }), {
        onSuccess: () => {
            showWorkLogDialog.value = false;
        },
    });
}
</script>
<template>
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Zeitkorrekturen vorhanden."
        @click:row="openWorkLog"
        :items="
            workLogs.map(log => ({
                id: log.id,
                user: log.user.first_name + ' ' + log.user.last_name,
                date: DateTime.fromSQL(log.start).toFormat('dd.MM.yyyy'),
            }))
        "
        :headers="[
            { title: 'Mitarbeiter', key: 'user' },
            { title: 'Datum', key: 'date' },
        ]"
    >
        <template v-slot:bottom>
            <v-pagination v-if="workLogs.length > 5" v-model="currentPage" :length="Math.ceil(workLogs.length / 5)"></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="workLogDialog" v-model="showWorkLogDialog" max-width="1000">
        <v-card :title="'Zeitkorrektur von ' + workLogDialog.user.first_name + ' ' + workLogDialog.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showWorkLogDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-data-table-virtual
                            :headers="[
                                { title: '', key: 'version', sortable: false },
                                { title: 'Start', key: 'start', sortable: false },
                                { title: 'Ende', key: 'end', sortable: false },
                                { title: 'Homeoffice', key: 'is_home_office', sortable: false },
                            ]"
                            :items="[
                                {
                                    version: 'Neuer Stand:',
                                    start: DateTime.fromSQL(workLogDialog.start).toFormat('dd.MM.yyyy HH:mm'),
                                    end: workLogDialog.end ? DateTime.fromSQL(workLogDialog.end).toFormat('dd.MM.yyyy HH:mm') : 'kein Ende',
                                    is_home_office: workLogDialog.is_home_office ? 'Ja' : 'Nein',
                                },
                            ]"
                        ></v-data-table-virtual>
                    </v-col>
                    <v-col cols="12">
                        <v-textarea v-model="workLogDialog.comment" readonly label="Bemerkung" variant="filled" rows="3"></v-textarea>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-col cols="12" class="d-flex justify-end ga-3">
                <v-btn
                    color="error"
                    :loading="changeStatusForm.processing && !changeStatusForm.accepted"
                    @click.stop="
                        changeStatusForm.accepted = false;
                        changeWorkLogStatus();
                    "
                >
                    Ablehnen
                </v-btn>
                <v-btn
                    color="success"
                    :loading="changeStatusForm.processing && changeStatusForm.accepted"
                    @click.stop="
                        changeStatusForm.accepted = true;
                        changeWorkLogStatus();
                    "
                >
                    Akzeptieren
                </v-btn>
            </v-col>
        </v-card>
    </v-dialog>
</template>
