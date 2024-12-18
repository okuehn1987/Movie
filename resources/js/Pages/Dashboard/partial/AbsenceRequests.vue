<script setup lang="ts">
import { Absence, AbsenceType, User } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type AbsenceProp = Pick<Absence, 'id' | 'start' | 'end' | 'user_id'> & {
    user: Pick<User, 'id' | 'first_name' | 'last_name'>;
    absence_type: Pick<AbsenceType, 'id' | 'name'>;
};

const props = defineProps<{
    absenceRequests: AbsenceProp[];
}>();

const absenceDialog = ref<AbsenceProp | null>(props.absenceRequests?.find(absence => absence.id == Number(route().params['openAbsence'])) ?? null);
const showAbsenceDialog = ref(!!absenceDialog.value);
const submitAbsenceSuccess = ref(false);

const currentPage = ref(1);

function openAbsence(_: unknown, row: { item: { id: Absence['id'] } }) {
    const absence = props.absenceRequests?.find(p => p.id === row.item.id);
    if (!absence) return;
    absenceDialog.value = absence;
    showAbsenceDialog.value = true;
}

function changeAbsenceStatus(accepted: boolean) {
    if (!absenceDialog.value) return;
    router.patch(
        route('absence.update', { absence: absenceDialog.value.id }),
        { accepted: accepted },
        {
            onSuccess: () => {
                showAbsenceDialog.value = false;
                submitAbsenceSuccess.value = true;
            },
        },
    );
}
</script>
<template>
    <v-card title="Abwesenheitsanträge">
        <v-data-table
            hover
            items-per-page="5"
            v-model:page="currentPage"
            no-data-text="keine Abwesenheitsanträge vorhanden."
            @click:row="openAbsence"
            :items="
                absenceRequests.map(absence => ({
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
            ><template v-slot:bottom>
                <v-pagination v-if="absenceRequests.length > 5" v-model="currentPage" :length="Math.ceil(absenceRequests.length / 5)"></v-pagination>
            </template>
        </v-data-table>

        <v-dialog v-if="absenceDialog" v-model="showAbsenceDialog" max-width="1000">
            <v-card :title="'Abwesenheit von ' + absenceDialog.user.first_name + ' ' + absenceDialog.user.last_name">
                <template #append>
                    <v-btn icon variant="text" @click="showAbsenceDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-row>
                        <v-col cols="12"
                            ><v-text-field label="Abwesenheitsgrund" v-model="absenceDialog.absence_type.name" readonly></v-text-field
                        ></v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                label="Von:"
                                :model-value="DateTime.fromSQL(absenceDialog.start).toFormat('dd.MM.yyyy')"
                                readonly
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field
                                label="Bis:"
                                :model-value="DateTime.fromSQL(absenceDialog.end).toFormat('dd.MM.yyyy')"
                                readonly
                            ></v-text-field>
                        </v-col>
                    </v-row>
                </v-card-text>

                <v-col cols="12" class="d-flex justify-end ga-3">
                    <v-btn color="error" @click.stop="changeAbsenceStatus(false)">Ablehnen</v-btn>
                    <v-btn color="success" @click.stop="changeAbsenceStatus(true)">Akzeptieren</v-btn>
                </v-col>
            </v-card>
        </v-dialog>
    </v-card>
</template>
