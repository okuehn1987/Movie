<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsencePatch, AbsenceType, RelationPick, User, WorkLogPatch } from '@/types/types';
import AbsenceRequests from './partial/AbsenceRequests.vue';
import WorkLogPatches from './partial/WorkLogPatches.vue';
import AbsencePatchRequests from './partial/AbsencePatchRequests.vue';

type WorkLogPatchProp = Pick<WorkLogPatch, 'id' | 'start' | 'end' | 'is_home_office' | 'user_id' | 'work_log_id' | 'comment'> &
    RelationPick<'workLogPatch', 'log', 'id' | 'start' | 'end' | 'is_home_office'> &
    RelationPick<'workLogPatch', 'user', 'id' | 'first_name' | 'last_name'>;

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'>;
type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id' | 'absence_id'>;

defineProps<{
    absenceRequests:
        | (AbsenceProp & {
              absence_type: Pick<AbsenceType, 'id' | 'name'>;
              usedDays: number;
              user: Pick<User, 'id' | 'first_name' | 'last_name'> & { usedLeaveDaysForYear: number; leaveDaysForYear: number };
          })[];
    absencePatchRequests:
        | (AbsencePatchProp & {
              absence_type: Pick<AbsenceType, 'id' | 'name'>;
              usedDays: number;
              user: Pick<User, 'id' | 'first_name' | 'last_name'> & { usedLeaveDaysForYear: number; leaveDaysForYear: number };
          })[];
    workLogPatchRequests: WorkLogPatchProp[];
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
                <v-card title="Abwesenheitskorrekturen">
                    <AbsencePatchRequests :absencePatchRequests="absencePatchRequests" />
                </v-card>
            </v-col>
            <v-col cols="12" sm="6">
                <v-card title="Zeitkorrekturen"><WorkLogPatches :patches="workLogPatchRequests" /></v-card>
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
