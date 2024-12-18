<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, OperatingSite, OperatingTime, Organization, SpecialWorkingHoursFactor, TimeAccountSetting } from '@/types/types';
import { ref } from 'vue';
import OrganizationSettings from './OrganizationSettings.vue';
import SWHFIndex from '../SWHF/SWHFIndex.vue';
import AbsenceTypeIndex from '../AbsenceType/AbsenceTypeIndex.vue';
import TimeAccountSettingsIndex from '../TimeAccount/TimeAccountSettingsIndex.vue';

defineProps<{
    organization: Organization;
    operating_sites: OperatingSite[];
    operating_times: OperatingTime[];
    special_working_hours_factors: SpecialWorkingHoursFactor[];
    absence_types: AbsenceType[];
    timeAccountSettings: TimeAccountSetting[];
}>();

const tab = ref<'Allgemeine Informationen' | 'Sonderarbeitszeitfaktor' | 'Abwesenheitsgründe' | 'Zeitkontoeinstellungen'>('Allgemeine Informationen');
</script>
<template>
    <AdminLayout :title="'Organisation ' + organization.name">
        <v-tabs v-model="tab" color="primary">
            <v-tab prepend-icon="mdi-account" text="Allgemeine Informationen" value="Allgemeine Informationen"></v-tab>
            <v-tab
                v-if="can('specialWorkingHoursFactors', 'viewIndex')"
                prepend-icon="mdi-clock-outline"
                text="Sonderarbeitszeitfaktor"
                value="Sonderarbeitszeitfaktor"
            ></v-tab>
            <v-tab
                v-if="can('absenceType', 'viewIndex')"
                prepend-icon="mdi-clock-outline"
                text="Abwesenheitsgründe"
                value="Abwesenheitsgründe"
            ></v-tab>
            <v-tab
                v-if="can('timeAccountSetting', 'viewIndex')"
                prepend-icon="mdi-clock-outline"
                text="Zeitkontoeinstellungen"
                value="Zeitkontoeinstellungen"
            ></v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab" class="w-100">
            <v-tabs-window-item value="Allgemeine Informationen">
                <OrganizationSettings :organization />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('specialWorkingHoursFactors', 'viewIndex')" value="Sonderarbeitszeitfaktor">
                <SWHFIndex :special_working_hours_factors />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('absenceType', 'viewIndex')" value="Abwesenheitsgründe">
                <AbsenceTypeIndex :absenceTypes="absence_types" />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('timeAccountSetting', 'viewIndex')" value="Zeitkontoeinstellungen">
                <TimeAccountSettingsIndex :timeAccountSettings />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
