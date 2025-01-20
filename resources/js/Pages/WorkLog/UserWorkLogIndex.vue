<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Paginator, User, WorkLog, WorkLogPatch } from '@/types/types';
import { usePagination } from '@/utils';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, toRefs } from 'vue';

type PatchProp = Omit<WorkLogPatch, 'deleted_at' | 'created_at' | 'user_id'>;

const props = defineProps<{
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
    workLogs: Paginator<
        WorkLog & {
            work_log_patches: PatchProp[];
        }
    >;
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'workLogs');

const showDialog = ref(false);

const patchMode = ref<'edit' | 'show' | null>(null);
const patchLog = ref<WorkLog | PatchProp | null>(null);
const inputVariant = computed(() => (patchLog.value ? 'plain' : 'underlined'));

const editableWorkLogs = computed(() => props.workLogs.data.filter((_, i) => i < 10 ** 10)); // 10 ** 10 to display for now but keep the feature

onMounted(() => {
    const workLogId = route().params['workLog'];
    if (workLogId) return editWorkLog(Number(workLogId) as WorkLog['id']);
});

const workLogForm = useForm({
    id: -1,
    start: new Date(),
    end: new Date(),
    comment: null as null | string,
    start_time: '',
    end_time: '',
    is_home_office: false,
});

function submit() {
    workLogForm
        .transform(d => {
            const start_time = DateTime.fromFormat(d.start_time, 'HH:mm');
            const end_time = DateTime.fromFormat(d.end_time, 'HH:mm');
            return {
                ...d,
                start: DateTime.fromISO(d.start.toISOString()).set({
                    hour: start_time.hour,
                    minute: start_time.minute,
                }),
                end: DateTime.fromISO(d.end.toISOString()).set({
                    hour: end_time.hour,
                    minute: end_time.minute,
                }),
                workLog: workLogForm.id,
            };
        })
        .post(route('workLogPatch.store'), {
            onSuccess: () => {
                showDialog.value = false;
            },
        });
}

function editWorkLog(id: WorkLog['id']) {
    const workLog = props.workLogs.data.find(e => e.id === id);
    if (!workLog) return;
    const lastPatch = workLog.work_log_patches.at(-1);
    if (!lastPatch) {
        patchLog.value = null;
        patchMode.value = 'edit';
    } else if (lastPatch && lastPatch.status === 'created') {
        patchLog.value = lastPatch;
        patchMode.value = 'show';
    }
    let start = workLog.start;
    let end = workLog.end;
    let isHomeOffice = workLog.is_home_office;
    if (patchLog.value) {
        start = patchLog.value.start;
        end = patchLog.value.end;
        isHomeOffice = patchLog.value.is_home_office;
        workLogForm.comment = 'comment' in patchLog.value ? patchLog.value?.comment : null;
    }

    workLogForm.id = id;
    workLogForm.start = new Date(start);
    workLogForm.end = end ? new Date(end) : new Date();
    workLogForm.start_time = DateTime.fromSQL(start).toFormat('HH:mm');
    workLogForm.end_time = DateTime.fromSQL(end || DateTime.now().toSQL()).toFormat('HH:mm');
    workLogForm.is_home_office = isHomeOffice;
    showDialog.value = true;
}

function retreatPatch() {
    const workLog = props.workLogs.data.find(e => e.id === workLogForm.id);
    if (!workLog) return;

    const lastPatch = workLog.work_log_patches.at(-1);
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
</script>
<template>
    <AdminLayout
        :title="user.first_name + ' ' + user.last_name"
        :backurl="route().params['fromUserWorkLogs'] ? route('workLog.index') : route('dashboard')"
    >
        <v-card>
            <v-data-table
                :headers="[
                    { title: 'Start', key: 'start' },
                    { title: 'Ende', key: 'end' },
                    { title: 'Dauer', key: 'duration' },
                    { title: 'Homeoffice', key: 'is_home_office' },
                    { title: 'Korrektur', key: 'status' },
                    {
                        title: '',
                        key: 'action',
                        sortable: false,
                        align: 'end',
                    },
                ]"
                :items="
                    data
                        .map(workLog => {
                            const lastAcceptedPatch = workLog.work_log_patches
                                .filter(e => e.status === 'accepted')
                                .toSorted((a, b) => (a.updated_at < b.updated_at ? 1 : -1))[0];
                            return {
                                ...(lastAcceptedPatch ?? workLog),
                                id: workLog.id,
                            };
                        })
                        .toSorted((a, b) => (a.start < b.start ? 1 : -1))
                        .map(workLog => ({
                            start: DateTime.fromSQL(workLog.start).toFormat('dd.MM.yyyy HH:mm'),
                            end: workLog.end ? DateTime.fromSQL(workLog.end).toFormat('dd.MM.yyyy HH:mm') : 'Noch nicht beendet',
                            duration: workLog.end ? DateTime.fromSQL(workLog.end).diff(DateTime.fromSQL(workLog.start)).toFormat('hh:mm') : '',
                            is_home_office: workLog.is_home_office ? 'Ja' : 'Nein',
                            id: workLog.id,
                            status: {
                                created: 'Beantragt',
                                declined: 'Abgelehnt',
                                accepted: 'Akzeptiert',
                                none: 'Nicht vorhanden',
                            }[workLogs.data.find(e => e.id === workLog.id)?.work_log_patches.at(-1)?.status || 'none'],
                        }))
                "
            >
                <template v-slot:item.action="{ item }">
                    <v-btn
                        v-if="editableWorkLogs.find(e => e.id === item.id) && can('workLogPatch', 'create')"
                        color="primary"
                        @click.stop="editWorkLog(item.id)"
                        :icon="
                            workLogs.data.find(log => log.id === item.id)?.work_log_patches.at(-1)?.status === 'created' ? 'mdi-eye' : 'mdi-pencil'
                        "
                        variant="text"
                        data-testid="eye"
                    >
                    </v-btn>
                </template>
                <template v-slot:bottom>
                    <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                </template>
            </v-data-table>

            <v-dialog max-width="1000" v-model="showDialog">
                <template v-slot:default="{ isActive }">
                    <v-card title="Zeitkorrektur">
                        <template #append>
                            <v-btn icon variant="text" @click="isActive.value = false">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </template>
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
                                        ></v-date-input>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-text-field
                                            type="time"
                                            label="Start"
                                            data-testid="userTimeCorrectionStartTime"
                                            required
                                            :error-messages="workLogForm.errors.start_time"
                                            v-model="workLogForm.start_time"
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
                                        ></v-date-input>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-text-field
                                            :disabled="!!patchLog"
                                            type="time"
                                            label="Ende"
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
                                            variant="filled"
                                            rows="3"
                                        >
                                        </v-textarea>
                                    </v-col>
                                    <v-col cols="12" md="3">
                                        <v-checkbox
                                            :disabled="!!patchLog"
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
                                            v-if="patchLog && can('workLogPatch', 'delete') && patchMode === 'show'"
                                            :loading="workLogForm.processing"
                                            @click.stop="retreatPatch"
                                            color="primary"
                                            >Antrag zurückziehen</v-btn
                                        >
                                        <v-btn
                                            v-else-if="can('workLogPatch', 'create') && patchMode === 'edit'"
                                            :loading="workLogForm.processing"
                                            type="submit"
                                            color="primary"
                                            >Korrektur beantragen</v-btn
                                        >
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
