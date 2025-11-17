<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, HomeOfficeDay, HomeOfficeDayGenerator, User } from '@/types/types';
import AbsencePatchRequests from './partial/AbsencePatchRequests.vue';
import AbsenceRequests from './partial/AbsenceRequests.vue';
import { AbsencePatchProp, AbsenceProp, HomeOfficeDayProp, UserProp, WorkLogPatchProp, WorkLogProp } from './partial/disputeTypes';
import WorkLogPatches from './partial/WorkLogPatches.vue';
import AbsenceDeleteRequests from './partial/AbsenceDeleteRequests.vue';
import WorkLogRequests from './partial/WorkLogRequests.vue';
import HomeOfficeDayRequests from './partial/HomeOfficeDayRequests.vue';

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
    homeOfficeDayRequests: (HomeOfficeDayProp & {
        user: Pick<User, 'id' | 'first_name' | 'last_name'>;
        homeOfficeDayGenerator: Pick<HomeOfficeDayGenerator, 'id' | 'start' | 'end' | 'created_as_request'>;
    })[];
    workLogPatchRequests?: WorkLogPatchProp[];
    workLogRequests?: WorkLogProp[];
}>();
</script>
<template>
    <AdminLayout title="Anträge">
        <v-alert
            type="info"
            v-if="
                [
                    ...absenceRequests,
                    ...(workLogPatchRequests ? workLogPatchRequests : []),
                    ...absenceDeleteRequests,
                    ...absencePatchRequests,
                    ...(workLogRequests ? workLogRequests : []),
                    ...homeOfficeDayRequests,
                ].length == 0
            "
        >
            Es gibt derzeit keine offenen Anträge
        </v-alert>
        <v-row v-else>
            <v-col cols="12" md="6">
                <v-row>
                    <v-col v-if="absenceRequests.length > 0" cols="12">
                        <v-card title="Abwesenheiten">
                            <AbsenceRequests :absenceRequests="absenceRequests" />
                        </v-card>
                    </v-col>
                    <v-col v-if="homeOfficeDayRequests.length > 0" cols="12">
                        <v-card title="HomeOffice">
                            <HomeOfficeDayRequests :homeOfficeDayRequests="homeOfficeDayRequests" />
                        </v-card>
                    </v-col>
                    <v-col v-if="workLogPatchRequests && workLogPatchRequests.length > 0" cols="12">
                        <v-card title="Zeitkorrekturen"><WorkLogPatches :patches="workLogPatchRequests" /></v-card>
                    </v-col>
                    <v-col v-if="absenceDeleteRequests.length > 0" cols="12">
                        <v-card title="Abwesenheitslöschungen">
                            <AbsenceDeleteRequests :requestedDeletes="absenceDeleteRequests" />
                        </v-card>
                    </v-col>
                    <!-- <v-col cols="12">
                        <v-card title="Arbeitszeitrahmenüberschreitung">
                            <v-card-text>TODO: to be implemented</v-card-text>
                        </v-card>
                    </v-col> -->
                </v-row>
            </v-col>
            <v-col cols="12" md="6">
                <v-row>
                    <v-col v-if="absencePatchRequests.length > 0" cols="12">
                        <v-card title="Abwesenheitskorrekturen">
                            <AbsencePatchRequests :absencePatchRequests="absencePatchRequests" />
                        </v-card>
                    </v-col>
                    <v-col v-if="workLogRequests && workLogRequests.length > 0" cols="12">
                        <v-card title="Manuelle Buchungsanträge"><WorkLogRequests :workLogs="workLogRequests" /></v-card>
                    </v-col>
                    <!-- 
                    <v-col cols="12">
                        <v-card title="Dienstreisen">
                            <v-card-text>TODO: to be implemented</v-card-text>
                        </v-card>
                    </v-col> -->
                </v-row>
            </v-col>
        </v-row>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
