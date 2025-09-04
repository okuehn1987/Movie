<script setup lang="ts">
import { DateString } from '@/types/types';
import { ref } from 'vue';

defineProps<{
    absences: { name: string; end: DateString; type: string }[];
}>();

const currentPage = ref(1);
</script>
<template>
    <v-card title="Aktuelle Abwesenheiten">
        <v-data-table
            hover
            items-per-page="5"
            v-model:page="currentPage"
            no-data-text="keine Abwesenheiten vorhanden."
            :items="absences"
            :headers="[
                { title: 'Mitarbeiter', key: 'name' },
                { title: 'Bis', key: 'end' },
                ...(absences.some(a => a.type) ? [{ title: 'Grund', key: 'type' }] : []),
            ]"
        >
            <template v-slot:bottom>
                <v-pagination v-if="absences.length > 5" v-model="currentPage" :length="Math.ceil(absences.length / 5)"></v-pagination>
            </template>
        </v-data-table>
    </v-card>
</template>
