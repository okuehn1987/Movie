<script setup lang="ts">
import { AbsenceType } from '@/types/types';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { AbsenceProp, UserProp } from './disputeTypes';

type AbsenceDeleteProp = AbsenceProp & {
    absence_type: Pick<AbsenceType, 'id' | 'name'>;
    user: UserProp;
};
const props = defineProps<{
    requestedDeletes: AbsenceDeleteProp[];
}>();

const absenceToDelete = ref<AbsenceDeleteProp | null>(
    props.requestedDeletes?.find(absence => absence.id == Number(route().params['openAbsenceDelete'])) ?? null,
);
const showAbsenceDialog = ref(!!absenceToDelete.value);

const currentPage = ref(1);

function openAbsencePatch(item: AbsenceDeleteProp) {
    const absencePatch = props.requestedDeletes?.find(p => p.id === item.id);
    if (!absencePatch) return;
    absenceToDelete.value = absencePatch;
    showAbsenceDialog.value = true;
}

const deleteAbsenceForm = useForm({});
function deleteAbsence() {
    if (!absenceToDelete.value) return;
    deleteAbsenceForm.delete(route('absence.destroy', { absence: absenceToDelete.value.id }), {
        onSuccess: () => {
            showAbsenceDialog.value = false;
        },
    });
}

const denyDeleteAbsenceForm = useForm({});
function denyAbsenceDelete() {
    if (!absenceToDelete.value) return;
    denyDeleteAbsenceForm.post(route('absence.denyDestroy', { absence: absenceToDelete.value.id }), {
        onSuccess: () => {
            showAbsenceDialog.value = false;
        },
    });
}
</script>
<template>
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Abwesenheitsanträge vorhanden."
        @click:row="(_:unknown,row:Record<'item',AbsenceDeleteProp>) => openAbsencePatch(row.item)"
        :items="
            requestedDeletes.map(absence => ({
                id: absence.id,
                user: absence.user.first_name + ' ' + absence.user.last_name,
                start: DateTime.fromSQL(absence.start).toFormat('dd.MM.yyyy'),
                end: DateTime.fromSQL(absence.end).toFormat('dd.MM.yyyy'),
            }))
        "
        :headers="[
            { title: 'Mitarbeiter', key: 'user' },
            { title: 'Von', key: 'start' },
            { title: 'Bis', key: 'end' },
        ]"
    >
        <template v-slot:bottom>
            <v-pagination v-if="requestedDeletes.length > 5" v-model="currentPage" :length="Math.ceil(requestedDeletes.length / 5)"></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="absenceToDelete" v-model="showAbsenceDialog" max-width="1000">
        <v-card :title="'Abwesenheit von ' + absenceToDelete.user.first_name + ' ' + absenceToDelete.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showAbsenceDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-text-field label="Abwesenheitsgrund" v-model="absenceToDelete.absence_type.name" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Von:"
                            :model-value="DateTime.fromSQL(absenceToDelete.start).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Bis:"
                            :model-value="DateTime.fromSQL(absenceToDelete.end).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-col cols="12" class="d-flex justify-end ga-2">
                <v-btn color="primary" @click.stop="denyAbsenceDelete()" :loading="deleteAbsenceForm.processing">Löschen ablehnen</v-btn>
                <v-btn color="error" @click.stop="deleteAbsence()" :loading="deleteAbsenceForm.processing">Abwesenheit Löschen</v-btn>
            </v-col>
        </v-card>
    </v-dialog>
</template>
