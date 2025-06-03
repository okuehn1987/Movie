<script setup lang="ts">
import { AbsencePatch, AbsenceType, User } from '@/types/types';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type AbsencePatchProp = Pick<AbsencePatch, 'id' | 'start' | 'end' | 'user_id' | 'absence_id'> & {
    absence_type: Pick<AbsenceType, 'id' | 'name'>;
    usedDays: number;
    user: Pick<User, 'id' | 'first_name' | 'last_name'> & { usedLeaveDaysForYear: number; leaveDaysForYear: number };
};

const props = defineProps<{
    absencePatchRequests: AbsencePatchProp[];
}>();

const absencePatchDialog = ref<AbsencePatchProp | null>(
    props.absencePatchRequests?.find(absencePatch => absencePatch.absence_id == Number(route().params['openAbsencePatch'])) ?? null,
);
const showAbsenceDialog = ref(!!absencePatchDialog.value);
const submitAbsenceSuccess = ref(false);

const currentPage = ref(1);

function openAbsencePatch(item: AbsencePatch) {
    const absencePatch = props.absencePatchRequests?.find(p => p.id === item.id);
    if (!absencePatch) return;
    absencePatchDialog.value = absencePatch;
    showAbsenceDialog.value = true;
}

function changeAbsenceStatus(accepted: boolean) {
    if (!absencePatchDialog.value) return;
    router.patch(
        route('absencePatch.updateStatus', { absencePatch: absencePatchDialog.value.id }),
        { accepted },
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
    <v-data-table
        hover
        items-per-page="5"
        v-model:page="currentPage"
        no-data-text="keine Abwesenheitsanträge vorhanden."
        @click:row="(_:unknown,row:Record<'item',AbsencePatch>) => openAbsencePatch(row.item)"
        :items="
            absencePatchRequests.map(absencePatch => ({
                id: absencePatch.id,
                user: absencePatch.user.first_name + ' ' + absencePatch.user.last_name,
                start: DateTime.fromSQL(absencePatch.start).toFormat('dd.MM.yyyy'),
                end: DateTime.fromSQL(absencePatch.end).toFormat('dd.MM.yyyy'),
            }))
        "
        :headers="[
            { title: 'Mitarbeiter', key: 'user' },
            { title: 'Von', key: 'start' },
            { title: 'Bis', key: 'end' },
        ]"
    >
        <template v-slot:bottom>
            <v-pagination
                v-if="absencePatchRequests.length > 5"
                v-model="currentPage"
                :length="Math.ceil(absencePatchRequests.length / 5)"
            ></v-pagination>
        </template>
    </v-data-table>

    <v-dialog v-if="absencePatchDialog" v-model="showAbsenceDialog" max-width="1000">
        <v-card :title="'Abwesenheit von ' + absencePatchDialog.user.first_name + ' ' + absencePatchDialog.user.last_name">
            <template #append>
                <v-btn icon variant="text" @click="showAbsenceDialog = false">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </template>
            <v-card-text>
                <v-row>
                    <v-col cols="12" v-if="absencePatchDialog.absence_type.name == 'Urlaub'">
                        <v-alert :type="absencePatchDialog.user.leaveDaysForYear < absencePatchDialog.usedDays ? 'warning' : 'info'" class="w-100">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <div>Verfügbare Urlaubstage {{ DateTime.now().year }}: {{ absencePatchDialog.user.leaveDaysForYear }}</div>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <div>Benötigte Urlaubstage: {{ absencePatchDialog.usedDays }}</div>
                                </v-col>
                            </v-row>
                        </v-alert>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field label="Abwesenheitsgrund" v-model="absencePatchDialog.absence_type.name" readonly></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Von:"
                            :model-value="DateTime.fromSQL(absencePatchDialog.start).toFormat('dd.MM.yyyy')"
                            readonly
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field
                            label="Bis:"
                            :model-value="DateTime.fromSQL(absencePatchDialog.end).toFormat('dd.MM.yyyy')"
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
</template>
