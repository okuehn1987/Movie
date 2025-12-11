<script setup lang="ts">
import { DateString } from '@/types/types';
import { ref } from 'vue';
import { useDisplay } from 'vuetify/lib/composables/display.mjs';

const props = defineProps<{
    absences: { last_name: string; first_name: string; start: DateString; end: DateString; type: string }[];
}>();

const currentPage = ref(1);
const display = useDisplay();

const sortBy = ref([
    { key: 'last_name', order: 'asc' },
    { key: 'first_name', order: 'asc' },
]);
</script>
<template>
    <v-card title="Abwesenheiten">
        <v-data-table
            hover
            items-per-page="5"
            v-model:page="currentPage"
            v-model:sort-by="sortBy"
            multi-sort
            no-data-text="keine Abwesenheiten vorhanden."
            :items="
                absences.map(a => ({
                    ...a,
                    name: a.first_name + ' ' + a.last_name,
                }))
            "
            :headers="[
                ...(display.smAndDown.value
                    ? [{ title: 'Mitarbeitende', key: 'name' }]
                    : [
                          { title: 'Vorname', key: 'first_name' },
                          { title: 'Nachname', key: 'last_name' },
                      ]),
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
