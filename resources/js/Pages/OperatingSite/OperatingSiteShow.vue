<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { OperatingSite, OperatingTime } from '@/types/types';
import AktuelleArbeitszeiten from './partials/AktuelleArbeitszeiten.vue';
import AllgemeineInformationen from './partials/AllgemeineInformationen.vue';
import { ref } from 'vue';

defineProps<{
    operatingSite: OperatingSite;
    operatingTimes: OperatingTime[];
}>();

const tab = ref('Allgemeine Informationen' as 'Allgemeine Informationen' | 'Betriebszeiten');
</script>

<template>
    <AdminLayout :title="'Betriebsstätte ' + operatingSite.name" :backurl="route('operatingSite.index')">
        <v-tabs v-model="tab" color="primary">
            <v-tab prepend-icon="mdi-account" text="Allgemeine Informationen" value="Allgemeine Informationen"></v-tab>
            <v-tab prepend-icon="mdi-clock-outline" text="Betriebszeiten" value="Betriebszeiten"></v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab" class="w-100">
            <v-tabs-window-item value="Allgemeine Informationen">
                <AllgemeineInformationen :operating-site="operatingSite" />
            </v-tabs-window-item>
            <v-tabs-window-item value="Betriebszeiten">
                <AktuelleArbeitszeiten :operating-times="operatingTimes" :operating-site-id="operatingSite.id" />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
