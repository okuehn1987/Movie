<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, OperatingTime, Timestamp, User, WorkLog, WorkLogPatch } from '@/types/types';
import Absences from './partial/Absences.vue';
import WorkingHours from './partial/WorkingHours.vue';
import { DateTime } from 'luxon';
import { roundTo } from '@/utils';
import { router } from '@inertiajs/vue3';

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
    lastWeekWorkLogs: (Omit<WorkLog, 'end'> & { end: Timestamp; duration: number })[];
}>();
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
                <v-card title="Zeiten der Letzten 7 tage">
                    <template #append>
                        <v-btn
                            icon="mdi-eye"
                            variant="text"
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
                        <v-data-table-virtual
                            :items="
                                lastWeekWorkLogs.map(w => ({
                                    ...w,
                                    start: DateTime.fromSQL(w.start).toFormat('dd.MM.yyyy HH:mm'),
                                    end: DateTime.fromSQL(w.end).toFormat('dd.MM.yyyy HH:mm'),
                                    duration: DateTime.fromSQL(w.end).diff(DateTime.fromSQL(w.start)).toFormat('hh:mm'),
                                }))
                            "
                        ></v-data-table-virtual>
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
