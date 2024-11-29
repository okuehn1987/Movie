<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { OperatingSite, OperatingTime } from '@/types/types';
import AktuelleArbeitszeiten from './partials/AktuelleArbeitszeiten.vue';
import AllgemeineInformationen from './partials/AllgemeineInformationen.vue';
import { ref } from 'vue';

defineProps<{
    operatingSite: OperatingSite & { operating_times: OperatingTime[] };
}>();

const tab = ref('Allgemeine Informationen');
</script>

<template>
    <AdminLayout :title="'Betriebsstätte ' + operatingSite.name" :backurl="route('operatingSite.index')">
        <v-tabs v-model="tab">
            <v-tab prepend-icon="mdi-account" text="Allgemeine Informationen" value="Allgemeine Informationen"></v-tab>
            <v-tab v-if="can('operatingTime', 'viewIndex')" prepend-icon="mdi-clock-outline" text="Betriebszeiten" value="Betriebszeiten"></v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab" class="w-100">
            <v-tabs-window-item value="Allgemeine Informationen">
                <AllgemeineInformationen :operating-site="operatingSite" />
            </v-tabs-window-item>
            <v-tabs-window-item v-if="can('operatingTime', 'viewIndex')" value="Betriebszeiten">
                <AktuelleArbeitszeiten :operatingSite />
            </v-tabs-window-item>
        </v-tabs-window>
    </AdminLayout>
</template>
