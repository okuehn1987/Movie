<script setup lang="ts">
import { AbsenceType, User } from '@/types/types';
import { AbsencePatchProp, AbsenceProp, UserProp } from '../utils';
import { router } from '@inertiajs/vue3';

const emit = defineEmits<{
    absenceReload: [];
}>();
const props = defineProps<{
    users: Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'>[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    selectedAbsence: AbsenceProp | AbsencePatchProp;
    absenceUser: UserProp;
}>();

const openModal = defineModel<boolean>({ required: true });

function openDispute() {
    if ('absence_id' in props.selectedAbsence) {
        router.get(
            route('dispute.index', {
                openAbsencePatch: props.selectedAbsence.absence_id,
            }),
        );
    } else {
        router.get(
            route('dispute.index', {
                openAbsence: props.selectedAbsence.id,
            }),
        );
    }
}
function withdrawRequest() {
    const options = {
        onSuccess: () => {
            openModal.value = false;
            emit('absenceReload');
        },
    };
    if ('absence_id' in props.selectedAbsence) {
        router.delete(route('absencePatch.destroy', { absencePatch: props.selectedAbsence.id }), options);
    } else {
        router.delete(route('absence.destroyDispute', { absence: props.selectedAbsence.id }), options);
    }
}
</script>
<template>
    <v-dialog max-width="1000" v-model="openModal">
        <template #default="{ isActive }">
            <v-card
                :title="
                    'Abwesenheit' +
                    (selectedAbsence.user_id != $page.props.auth.user.id
                        ? ' von ' +
                          users.map(u => ({ ...u, name: u.first_name + ' ' + u.last_name })).find(u => u.id === selectedAbsence.user_id)?.name
                        : '') +
                    ' beantragen'
                "
            >
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form readonly>
                        <v-row>
                            <v-col cols="12">
                                <v-select
                                    label="Abwesenheitsgrund angeben"
                                    :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                                    :model-value="selectedAbsence.absence_type_id"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="Von" :model-value="selectedAbsence.start"></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field type="date" label="Bis" :model-value="selectedAbsence.end"></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn v-if="can('user', 'viewDisputes')" @click.stop="openDispute" type="button" color="primary">
                                    Antrag öffnen
                                </v-btn>
                                <v-btn
                                    v-else-if="can('absence', 'deleteDispute', selectedAbsence)"
                                    @click.stop="withdrawRequest"
                                    type="button"
                                    color="primary"
                                >
                                    Antrag zurückziehen
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
