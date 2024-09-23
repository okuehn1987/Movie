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
import WorkTimeSettings from "./WorkTimeSettings.vue";
defineProps<{
    organization: Organization;
    operating_sites: OperatingSite[];
    operating_times: OperatingTime[];
    special_working_hours_factors: SpecialWorkingHoursFactor[];
    absence_types: AbsenceType[];
}>();

const tab = ref<"Allgemeine Informationen" | "Arbeitszeiten">(
    "Allgemeine Informationen"
);
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
                            text="Arbeitszeiten"
                            value="Arbeitszeiten"
                        ></v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="tab" class="w-100">
                        <v-tabs-window-item value="Allgemeine Informationen">
                            <OrganizationSettings
                                :organization
                            ></OrganizationSettings>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="Arbeitszeiten">
                            <WorkTimeSettings></WorkTimeSettings
                        ></v-tabs-window-item>
                    </v-tabs-window>
                </div>
            </v-card>
        </v-container>
    </AdminLayout>
</template>
