<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, DateTimeString, OperatingTime, User, WorkLog, WorkLogPatch } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import Absences from './partial/Absences.vue';
import WorkingHours from './partial/WorkingHours.vue';
import { ref } from 'vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id'> & {
    work_log: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'>;

defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    patches: PatchProp[] | null;
    operating_times: OperatingTime[];
    overtime: number;
    workingHours: { should: number; current: number; currentHomeOffice: number };
    currentAbsences: (AbsenceProp & {
        user: Pick<User, 'id' | 'first_name' | 'last_name'>;
        absence_type: Pick<AbsenceType, 'id' | 'abbreviation'>;
    })[];
    lastWeekWorkLogs: (Omit<WorkLog, 'end'> & { end: DateTimeString; current_accepted_patch: WorkLogPatch | null })[];
}>();

const currentPage = ref(1);
</script>

<template>
    <AdminLayout :title="'Dashboard von ' + $page.props.auth.user.first_name + ' ' + $page.props.auth.user.last_name">
        <v-row>
            <v-col cols="12" lg="6">
                <WorkingHours :lastWorkLog :operating_times :overtime :workingHours />
            </v-col>
            <v-col cols="12" lg="6">
                <Absences :absences="currentAbsences" />
            </v-col>
            <v-col cols="12" lg="6">
                <v-card title="Zeiten der letzten 7 Tage">
                    <template #append>
                        <v-btn
                            icon="mdi-eye"
                            variant="text"
                            title="Alle Zeiten anzeigen"
                            data-testid="workingHours"
                            @click="
                                router.get(
                                    route('user.workLog.index', {
                                        user: $page.props.auth.user.id,
                                    }),
                                )
                            "
                        />
                    </template>
                    <v-card-text>
                        <v-data-table
                            v-model:page="currentPage"
                            items-per-page="5"
                            :items="
                                lastWeekWorkLogs.map(w => ({
                                    ...w,
                                    start: DateTime.fromSQL(w.current_accepted_patch?.start ?? w.start).toFormat('dd.MM.yyyy HH:mm'),
                                    end: DateTime.fromSQL(w.current_accepted_patch?.end ?? w.end).toFormat('dd.MM.yyyy HH:mm'),
                                    duration: DateTime.fromSQL(w.current_accepted_patch?.end ?? w.end)
                                        .diff(DateTime.fromSQL(w.current_accepted_patch?.start ?? w.start))
                                        .toFormat('h:mm'),
                                }))
                            "
                            :headers="[
                                { title: 'Start', key: 'start' },
                                { title: 'Ende', key: 'end' },
                                { title: 'Dauer', key: 'duration' },
                            ]"
                        >
                            <template #bottom>
                                <v-pagination
                                    v-if="lastWeekWorkLogs.length > 5"
                                    v-model="currentPage"
                                    :length="Math.ceil(lastWeekWorkLogs.length / 5)"
                                ></v-pagination>
                            </template>
                        </v-data-table>
                    </v-card-text>
                </v-card>
            </v-col>
            <!-- <v-col cols="12" md="12" lg="6" xl="4" v-if="supervisor">
                    <v-card title="Vorgesetzter">
                        <v-card-text>
                            {{ supervisor.first_name }}
                            {{ supervisor.last_name }}
                        </v-card-text>
                    </v-card>
                </v-col> -->
            <!-- <v-col cols="12" sm="6" lg="4">
                    <v-card>
                        <v-card-title>Informationen</v-card-title>
                        <v-card-item>TODO: to be implemented</v-card-item>
                    </v-card>
                </v-col> -->
        </v-row>
    </AdminLayout>
</template>
