<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, OperatingTime, User, WorkLog, WorkLogPatch } from '@/types/types';
import WorkingHours from './partial/WorkingHours.vue';
import WorkLogPatches from './partial/WorkLogPatches.vue';
import AbsenceRequests from './partial/AbsenceRequests.vue';
import Absences from './partial/Absences.vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id'> & {
    work_log: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'> & {
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    patches: PatchProp[] | null;
    operating_times: OperatingTime[];
    overtime: number;
    workingHours: { should: number; current: number; currentHomeOffice: number };
    absenceRequests: (AbsenceProp & { absence_type: Pick<AbsenceType, 'id' | 'name'> })[] | null;
    currentAbsences: (AbsenceProp & { absence_type: Pick<AbsenceType, 'id' | 'abbreviation'> })[];
}>();
</script>

<template>
    <AdminLayout :title="'Dashboard von ' + $page.props.auth.user.first_name + ' ' + $page.props.auth.user.last_name">
        <v-row>
            <v-col cols="12" sm="6" lg="4">
                <WorkingHours :lastWorkLog :operating_times :overtime :workingHours />
            </v-col>

            <v-col cols="12" sm="6" lg="4" v-if="supervisor">
                <v-card title="Vorgesetzter">
                    <v-card-text>
                        {{ supervisor.first_name }}
                        {{ supervisor.last_name }}
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- <v-col cols="12" sm="6" lg="4">
                <v-card>
                    <v-card-title>Informationen</v-card-title>
                    <v-card-item>TODO: to be implemented</v-card-item>
                </v-card>
            </v-col> -->
            <v-col cols="12" sm="6" lg="4" v-if="patches">
                <WorkLogPatches :patches="patches" />
            </v-col>
            <v-col cols="12" sm="6" lg="4">
                <Absences :absences="currentAbsences" />
            </v-col>
            <v-col cols="12" sm="6" lg="4" v-if="absenceRequests">
                <AbsenceRequests :absenceRequests="absenceRequests" />
            </v-col>
        </v-row>
    </AdminLayout>
</template>
