<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, OperatingTime, User, WorkLog, WorkLogPatch } from '@/types/types';
import { usePage } from '@inertiajs/vue3';
import Arbeitszeit from './partial/Arbeitszeit.vue';
import Zeitkorrekturen from './partial/Zeitkorrekturen.vue';
import Abwesenheiten from './partial/Abwesenheiten.vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id'> & {
    work_log: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
};

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id'> & {
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
    absence_type: Pick<AbsenceType, 'id' | 'name'>;
};

defineProps<{
    lastWorkLog: Pick<WorkLog, 'id' | 'start' | 'end' | 'is_home_office'>;
    supervisor: Pick<User, 'id' | 'first_name' | 'last_name'>;
    patches: PatchProp[] | null;
    operating_times: OperatingTime[];
    overtime: number;
    workingHours: { should: number; current: number };
    absences: AbsenceProp[] | null;
}>();
const page = usePage();
</script>

<template>
    <AdminLayout :title="'Dashboard von ' + $page.props.auth.user.first_name + ' ' + $page.props.auth.user.last_name">
        <v-row>
            <v-col cols="12" sm="6" lg="4">
                <Arbeitszeit :lastWorkLog :operating_times :overtime :workingHours />
            </v-col>

            <v-col cols="12" sm="6" lg="4" v-if="supervisor">
                <v-card title="Vorgesetzter">
                    <v-card-text>
                        {{ supervisor?.first_name }}
                        {{ supervisor?.last_name }}
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" lg="4">
                <v-card>
                    <v-card-title>Informationen</v-card-title>
                    <v-card-item>TODO: to be implemented</v-card-item>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" lg="4" v-if="page.props.auth.user.work_log_patching && patches">
                <Zeitkorrekturen :patches="patches" />
            </v-col>
            <v-col cols="12" sm="6" lg="4" v-if="absences">
                <Abwesenheiten :absences="absences" />
            </v-col>
        </v-row>
    </AdminLayout>
</template>
