<script setup lang="ts">
import ConfirmDelete from '@/Components/ConfirmDelete.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Relation, User, WorkLog } from '@/types/types';
import { useMaxScrollHeight } from '@/utils';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed, onMounted, ref } from 'vue';
import CreateNewWorkLog from './CreateNewWorkLog.vue';

type patchkeys = 'id' | 'work_log_id' | 'status' | 'start' | 'end' | 'is_home_office' | 'comment';

const props = defineProps<{
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
    workLogs: (WorkLog & {
        latest_patch: Relation<'workLog', 'latest_patch', patchkeys>;
        current_accepted_patch: Relation<'workLog', 'current_accepted_patch', patchkeys>;
    })[];
}>();

const showDialog = ref(false);

const patchMode = ref<'edit' | 'show' | null>(null);
const patchLog = ref<Relation<'workLog', 'current_accepted_patch', patchkeys>>(null);
const inputVariant = computed(() => (patchMode.value == 'show' ? 'plain' : 'underlined'));

onMounted(() => {
    const workLogId = route().params['workLog'];
    if (workLogId) return editWorkLog(Number(workLogId) as WorkLog['id']);

    const workLogPatchId = Number(route().params['openWorkLogPatch']);
    if (workLogPatchId) {
        const workLog = props.workLogs.find(w => w.latest_patch?.id == workLogPatchId || w.current_accepted_patch?.id == workLogPatchId);
        if (workLog) return editWorkLog(workLog.id);
    }
});

const workLogForm = useForm({
    id: null as WorkLog['id'] | null,
    start: new Date(),
    end: new Date(),
    comment: null as null | string,
    start_time: '',
    end_time: '',
    is_home_office: false,
    type: 'log' as 'log' | 'patch',
    status: 'created' as WorkLog['status'],
});

function parseTimeFlexible(s: string) {
    let t = DateTime.fromFormat(s, 'HH:mm:ss');
    if (!t.isValid) t = DateTime.fromFormat(s, 'HH:mm');
    return t;
}

function submit() {
    workLogForm
        .transform(d => {
            const start_time = parseTimeFlexible(d.start_time);
            const end_time = parseTimeFlexible(d.end_time);
            return {
                ...d,
                start: DateTime.fromISO(d.start.toISOString()).set({
                    hour: start_time.hour,
                    minute: start_time.minute,
                    second: start_time.second,
                }),
                end: DateTime.fromISO(d.end.toISOString()).set({
                    hour: end_time.hour,
                    minute: end_time.minute,
                    second: end_time.second,
                }),
                workLog: workLogForm.id,
            };
        })
        .post(route('workLogPatch.store'), {
            onSuccess: () => {
                showDialog.value = false;
                workLogForm.reset();
            },
        });
}

function editWorkLog(id: WorkLog['id']) {
    const workLog = props.workLogs.find(e => e.id === id);
    if (!workLog) return;
    const lastPatch = workLog.latest_patch;
    if (!lastPatch) {
        patchLog.value = null;
        patchMode.value = workLog.status == 'created' ? 'show' : 'edit';
    } else {
        if (lastPatch.status === 'created') {
            patchLog.value = lastPatch;
            patchMode.value = 'show';
        } else if (lastPatch.status === 'accepted') {
            patchLog.value = lastPatch;
            patchMode.value = 'edit';
        } else {
            patchLog.value = lastPatch;
            patchMode.value = 'edit';
        }
    }
    const start = (patchLog.value ?? workLog).start;
    const end = (patchLog.value ?? workLog).end;

    workLogForm.defaults({
        comment: patchLog.value?.comment ?? workLog.comment,
        id: id,
        start: new Date(start),
        end: end ? new Date(end) : new Date(),
        start_time: DateTime.fromSQL(start).toFormat('HH:mm:ss'),
        end_time: DateTime.fromSQL(end || DateTime.now().toSQL()).toFormat('HH:mm:ss'),
        is_home_office: (patchLog.value ?? workLog).is_home_office,
        type: patchLog.value ? 'patch' : 'log',
        status: (patchLog.value ?? workLog).status,
    });

    workLogForm.reset();

    showDialog.value = true;
}

function retreatPatch() {
    const workLog = props.workLogs.find(e => e.id === workLogForm.id);
    if (!workLog) return;

    const lastPatch = workLog.latest_patch;
    if (!lastPatch) return;

    router.delete(
        route('workLogPatch.destroy', {
            workLogPatch: lastPatch.id,
        }),
        {
            onSuccess: () => {
                showDialog.value = false;
            },
        },
    );
}

const tableHeight = useMaxScrollHeight(0);
</script>
<template>
    <AdminLayout
        :title="user.first_name + ' ' + user.last_name"
        :backurl="route().params['fromUserWorkLogs'] ? route('workLog.index') : route('dashboard')"
    >
        <v-card>
            <v-data-table-virtual
                fixed-header
                :style="{ maxHeight: tableHeight }"
                :headers="[
                    { title: 'Start', key: 'start_text', width: '20%' },
                    { title: 'Ende', key: 'end', width: '20%' },
                    { title: 'Dauer', key: 'duration', width: '20%' },
                    { title: 'Homeoffice', key: 'is_home_office', width: '20%' },
                    { title: 'Stand', key: 'displayStatus', width: '20%' },
                    {
                        title: '',
                        key: 'actions',
                        sortable: false,
                        width: '1px',
                        align: 'end',
                    },
                ]"
                :items="
                    workLogs
                        .map(workLog => {
                            const data = workLog.current_accepted_patch ?? workLog;

                            return {
                                start: data.start,
                                start_text: DateTime.fromSQL(data.start).toFormat('dd.MM.yyyy HH:mm'),
                                end: data.end ? DateTime.fromSQL(data.end).toFormat('dd.MM.yyyy HH:mm') : 'Noch nicht beendet',
                                duration: data.end ? DateTime.fromSQL(data.end).diff(DateTime.fromSQL(data.start)).toFormat('hh:mm') : '',
                                is_home_office: data.is_home_office ? 'Ja' : 'Nein',
                                id: workLog.id,
                                displayStatus: workLog.latest_patch
                                    ? {
                                          created: 'Korrektur Beantragt',
                                          declined: 'Korrektur Abgelehnt',
                                          accepted: 'Korrektur Akzeptiert',
                                      }[workLog.latest_patch.status]
                                    : {
                                          created: 'Beantragt',
                                          declined: 'Abgelehnt',
                                          accepted: 'Akzeptiert',
                                      }[workLog.status],
                                status: (workLog.latest_patch ?? workLog).status,
                            };
                        })
                        .toSorted((a, b) => b.start.localeCompare(a.start))
                "
            >
                <template v-slot:header.actions>
                    <CreateNewWorkLog :user></CreateNewWorkLog>
                </template>
                <template v-slot:item.actions="{ item }">
                    <div class="d-flex ga-2">
                        <v-btn
                            v-if="can('workLogPatch', 'create')"
                            color="primary"
                            @click.stop="editWorkLog(item.id)"
                            :icon="item.status === 'created' ? 'mdi-eye' : 'mdi-pencil'"
                            variant="text"
                            data-testid="entryToWorkLog"
                        ></v-btn>
                        <ConfirmDelete
                            v-if="can('workLog', 'delete')"
                            title="Eintrag löschen"
                            content="Möchten Sie diesen Eintrag wirklich löschen? Dies kann nicht rückgängig gemacht werden."
                            :route="route('workLog.destroy', { workLog: item.id })"
                        ></ConfirmDelete>
                    </div>
                </template>
            </v-data-table-virtual>

            <v-dialog max-width="1000" v-model="showDialog">
                <template v-slot:default="{ isActive }">
                    <v-card :title="workLogForm.type == 'patch' ? 'Zeitkorrektur' : 'Buchung'">
                        <template #append>
                            <v-btn
                                icon
                                variant="text"
                                @click="
                                    isActive.value = false;
                                    workLogForm.reset();
                                "
                            >
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </template>
                        <v-divider></v-divider>
                        <v-card-text>
                            <v-form @submit.prevent="submit">
                                <v-row>
                                    <v-col cols="12" md="3">
                                        <v-date-input
                                            label="Start"
                                            data-testid="userTimeCorrectionStartDay"
                                            required
                                            :error-messages="workLogForm.errors.start"
                                            v-model="workLogForm.start"
                                            :variant="inputVariant"
                                            style="height: 73px"
                                            :disabled="workLogForm.status == 'created'"
                                        ></v-date-input>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-text-field
                                            type="time"
                                            label="Start"
                                            step="1"
                                            data-testid="userTimeCorrectionStartTime"
                                            required
                                            :error-messages="workLogForm.errors.start_time"
                                            v-model="workLogForm.start_time"
                                            :disabled="workLogForm.status == 'created'"
                                            :variant="inputVariant"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-date-input
                                            label="Ende"
                                            data-testid="userTimeCorrectionEndDay"
                                            required
                                            :error-messages="workLogForm.errors.end"
                                            v-model="workLogForm.end"
                                            :variant="inputVariant"
                                            style="height: 73px"
                                            :disabled="workLogForm.status == 'created'"
                                        ></v-date-input>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-text-field
                                            :disabled="workLogForm.status == 'created'"
                                            type="time"
                                            label="Ende"
                                            step="1"
                                            data-testid="userTimeCorrectionEndTime"
                                            required
                                            :error-messages="workLogForm.errors.end_time"
                                            v-model="workLogForm.end_time"
                                            :variant="inputVariant"
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-textarea
                                            label="Bemerkung (optional)"
                                            v-model="workLogForm.comment"
                                            :error-messages="workLogForm.errors.comment"
                                            :disabled="workLogForm.status == 'created'"
                                            variant="filled"
                                            rows="3"
                                        ></v-textarea>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-checkbox
                                            :disabled="workLogForm.status == 'created'"
                                            label="Homeoffice"
                                            required
                                            :error-messages="workLogForm.errors.is_home_office"
                                            v-model="workLogForm.is_home_office"
                                            :variant="inputVariant"
                                            hide-details
                                        ></v-checkbox>
                                    </v-col>

                                    <v-col cols="12" class="text-end">
                                        <v-btn
                                            v-if="workLogForm.status == 'created' && can('workLogPatch', 'delete') && patchMode === 'show'"
                                            :loading="workLogForm.processing"
                                            @click.stop="retreatPatch"
                                            color="primary"
                                        >
                                            Antrag zurückziehen
                                        </v-btn>
                                        <v-btn
                                            v-else-if="can('workLogPatch', 'create') && patchMode === 'edit'"
                                            :loading="workLogForm.processing"
                                            :disabled="!workLogForm.isDirty"
                                            type="submit"
                                            color="primary"
                                        >
                                            Korrektur {{ can('workLogPatch', 'update') ? 'speichern' : 'beantragen' }}
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-form>
                        </v-card-text>
                    </v-card>
                </template>
            </v-dialog>
        </v-card>
    </AdminLayout>
</template>
