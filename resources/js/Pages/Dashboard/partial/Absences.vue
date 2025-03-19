<script setup lang="ts">
import { Absence, RelationPick } from '@/types/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id' | 'absence_type_id'> &
    RelationPick<'absence', 'absence_type', 'id' | 'abbreviation', true> &
    RelationPick<'absence', 'user', 'id' | 'first_name' | 'last_name'>;

defineProps<{
    absences: AbsenceProp[];
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
            :items="
                absences.map(absence => ({
                    id: absence.id,
                    user: absence.user.first_name + ' ' + absence.user.last_name,
                    start: DateTime.fromSQL(absence.start).toFormat('dd.MM.'),
                    end: DateTime.fromSQL(absence.end).toFormat('dd.MM.'),
                    absenceType: absence.absence_type?.abbreviation ?? '-',
                }))
            "
            :headers="[
                { title: 'Mitarbeiter', key: 'user' },
                { title: 'Von', key: 'start' },
                { title: 'Bis', key: 'end' },
                ...(absences.some(a => a.absence_type_id) ? [{ title: 'Grund', key: 'absenceType' }] : []),
            ]"
            ><template v-slot:bottom>
                <v-pagination v-if="absences.length > 5" v-model="currentPage" :length="Math.ceil(absences.length / 5)"></v-pagination>
            </template>
        </v-data-table>
    </v-card>
</template>
