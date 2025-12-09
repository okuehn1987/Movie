<script setup lang="ts">
import { DateString } from '@/types/types';
import { ref } from 'vue';
import { useDisplay } from 'vuetify/lib/composables/display.mjs';

defineProps<{
    absences: { name: string; start: DateString; end: DateString; type: string }[];
}>();

const currentPage = ref(1);
const display = useDisplay();
</script>
<template>
    <v-card title="Abwesenheiten">
        <v-data-table
            hover
            items-per-page="5"
            v-model:page="currentPage"
            no-data-text="keine Abwesenheiten vorhanden."
            :items="absences"
            :headers="[
                { title: 'Mitarbeitende', key: 'name' },
                ...(display.smAndDown.value ? [] : [{ title: 'von', key: 'start' }]),
                { title: 'bis', key: 'end' },
                ...(absences.some(a => a.type) ? [{ title: 'Grund', key: 'type' }] : []),
            ]"
        >
            <template v-slot:bottom>
                <v-pagination v-if="absences.length > 5" v-model="currentPage" :length="Math.ceil(absences.length / 5)"></v-pagination>
            </template>
        </v-data-table>
    </v-card>
</template>
