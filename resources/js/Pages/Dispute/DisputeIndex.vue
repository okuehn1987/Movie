<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, RelationPick, WorkLogPatch } from '@/types/types';
import AbsenceRequests from '../Dashboard/partial/AbsenceRequests.vue';
import WorkLogPatches from '../Dashboard/partial/WorkLogPatches.vue';

type PatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id' | 'comment'> &
    RelationPick<'workLogPatch', 'log', 'id' | 'start' | 'end' | 'is_home_office'> &
    RelationPick<'workLogPatch', 'user', 'id' | 'first_name' | 'last_name'>;

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'>;

defineProps<{
    absenceRequests:
        | (AbsenceProp &
              RelationPick<'absence', 'user', 'id' | 'first_name' | 'last_name', false, { usedLeaveDaysForYear: number; leaveDaysForYear: number }> &
              RelationPick<'absence', 'absence_type', 'id' | 'name'> & {
                  usedDays: number;
              })[];
    patches: PatchProp[];
}>();
</script>
<template>
    <AdminLayout title="Anträge">
        <v-row>
            <v-col cols="12" sm="6">
                <v-card title="Abwesenheiten">
                    <AbsenceRequests :absenceRequests="absenceRequests" />
                </v-card>
            </v-col>
            <v-col cols="12" sm="6">
                <v-card title="Zeitkorrekturen"> <WorkLogPatches :patches="patches" /> </v-card>
            </v-col>
            <v-col cols="12" sm="6">
                <v-card title="Dienstreisen">
                    <v-card-text>TODO: to be implemented</v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6">
                <v-card title="Arbeitszeitrahmenüberschreitung">
                    <v-card-text>TODO: to be implemented</v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
