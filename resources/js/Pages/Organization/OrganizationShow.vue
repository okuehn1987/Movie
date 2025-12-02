<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, Flag, OperatingSite, Organization, SpecialWorkingHoursFactor, TimeAccountSetting } from '@/types/types';
import { ref } from 'vue';
import OrganizationGeneralInformationSettings from './OrganizationGeneralInformationSettings.vue';
import SWHFIndex from '../SWHF/SWHFIndex.vue';
import AbsenceTypeIndex from '../AbsenceType/AbsenceTypeIndex.vue';
import TimeAccountSettingsIndex from '../TimeAccount/TimeAccountSettingsIndex.vue';
import OrganizationSettings from './OrganizationSettings.vue';

defineProps<{
    organization: Organization;
    flags: Record<Flag, string>;
    operating_sites: OperatingSite[];
    special_working_hours_factors: SpecialWorkingHoursFactor[];
    absence_types: AbsenceType[];
    absence_type_defaults: string[];
    timeAccountSettings: TimeAccountSetting[];
}>();

const tab = ref<'Allgemeine Informationen' | 'Einstellungen' | 'Sonderarbeitszeitfaktor' | 'Abwesenheitsgründe' | 'Zeitkontoeinstellungen'>(
    'Allgemeine Informationen',
);
</script>
<template>
    <AdminLayout :title="'Organisation ' + organization.name">
        <v-tabs v-model="tab" color="primary">
            <v-tab prepend-icon="mdi-account" text="Allgemeine Informationen" value="Allgemeine Informationen"></v-tab>
            <v-tab prepend-icon="mdi-cog" text="Einstellungen" value="Einstellungen"></v-tab>
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
                <OrganizationGeneralInformationSettings :organization />
            </v-tabs-window-item>
            <v-tabs-window-item value="Einstellungen">
                <OrganizationSettings :organization :flags />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('specialWorkingHoursFactors', 'viewIndex')" value="Sonderarbeitszeitfaktor">
                <SWHFIndex :special_working_hours_factors />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('absenceType', 'viewIndex')" value="Abwesenheitsgründe">
                <AbsenceTypeIndex :absenceTypes="absence_types" :absence_type_defaults />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('timeAccountSetting', 'viewIndex')" value="Zeitkontoeinstellungen">
                <TimeAccountSettingsIndex :timeAccountSettings />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
