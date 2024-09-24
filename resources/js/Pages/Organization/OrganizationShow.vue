<script setup lang="ts">
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    AbsenceType,
    OperatingSite,
    OperatingTime,
    Organization,
    SpecialWorkingHoursFactor,
} from "@/types/types";
import { ref } from "vue";
import OrganizationSettings from "./OrganizationSettings.vue";
import SWHFSettings from "./SWHFSettings.vue";
import AbsenceTypeIndex from "../AbsenceType/AbsenceTypeIndex.vue";

defineProps<{
    organization: Organization;
    operating_sites: OperatingSite[];
    operating_times: OperatingTime[];
    special_working_hours_factors: SpecialWorkingHoursFactor[];
    absence_types: AbsenceType[];
}>();

const tab = ref<
    | "Allgemeine Informationen"
    | "Sonderarbeitszeitfaktor"
    | "Abwesenheitsgründe"
>("Allgemeine Informationen");
</script>
<template>
    <AdminLayout title="Organisation">
        <v-container>
            <v-card>
                <v-toolbar color="primary" :title="organization.name">
                </v-toolbar>
                <div class="d-flex flex-row">
                    <v-tabs v-model="tab" color="primary" direction="vertical">
                        <v-tab
                            prepend-icon="mdi-account"
                            text="Allgemeine Informationen"
                            value="Allgemeine Informationen"
                        ></v-tab>
                        <v-tab
                            prepend-icon="mdi-clock-outline"
                            text="Sonderarbeitszeitfaktor"
                            value="Sonderarbeitszeitfaktor"
                        ></v-tab>
                        <v-tab
                            prepend-icon="mdi-clock-outline"
                            text="Abwesenheitsgründe"
                            value="Abwesenheitsgründe"
                        ></v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="tab" class="w-100">
                        <v-tabs-window-item value="Allgemeine Informationen">
                            <OrganizationSettings
                                :organization
                            ></OrganizationSettings>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="Sonderarbeitszeitfaktor">
                            <SWHFSettings
                                :special_working_hours_factors
                            ></SWHFSettings>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="Abwesenheitsgründe">
                            <AbsenceTypeIndex
                                :absenceTypes="absence_types"
                            ></AbsenceTypeIndex>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </div>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
