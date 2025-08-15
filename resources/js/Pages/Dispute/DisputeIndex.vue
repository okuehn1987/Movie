<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType } from '@/types/types';
import AbsencePatchRequests from './partial/AbsencePatchRequests.vue';
import AbsenceRequests from './partial/AbsenceRequests.vue';
import { AbsencePatchProp, AbsenceProp, UserProp, WorkLogPatchProp } from './partial/disputeTypes';
import WorkLogPatches from './partial/WorkLogPatches.vue';
import AbsenceDeleteRequests from './partial/AbsenceDeleteRequests.vue';

defineProps<{
    absenceRequests: (AbsenceProp & {
        absence_type: Pick<AbsenceType, 'id' | 'name'>;
        usedDays: number;
        user: UserProp & {
            usedLeaveDaysForYear: number;
            leaveDaysForYear: number;
        };
    })[];
    absencePatchRequests: AbsencePatchProp[];
    absenceDeleteRequests: (AbsenceProp & {
        absence_type: Pick<AbsenceType, 'id' | 'name'>;
        user: UserProp;
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
                <v-card title="Abwesenheitslöschungen">
                    <AbsenceDeleteRequests :requestedDeletes="absenceDeleteRequests" />
                </v-card>
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
