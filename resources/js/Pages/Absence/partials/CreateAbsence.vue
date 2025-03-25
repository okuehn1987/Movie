<script setup lang="ts">
import { AbsenceType, User } from '@/types/types';
import { InertiaForm } from '@inertiajs/vue3';

defineProps<{
    users: Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'>[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
}>();

const openModal = defineModel<boolean>({ required: true });
const absenceForm = defineModel<
    InertiaForm<{ user_id: User['id']; start: string | null; end: string | null; absence_type_id: AbsenceType['id'] | null }>
>('absenceForm', { required: true });
</script>
<template>
    <v-dialog max-width="1000" v-model="openModal">
        <template #default="{ isActive }">
            <v-card
                :title="
                    'Abwesenheit' +
                    (absenceForm.user_id != $page.props.auth.user.id
                        ? ' von ' + users.map(u => ({ ...u, name: u.first_name + ' ' + u.last_name })).find(u => u.id === absenceForm.user_id)?.name
                        : '') +
                    ' beantragen'
                "
                ><template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text v-if="absenceForm.errors.user_id">
                    <v-alert color="error" closable>{{ absenceForm.errors.user_id }}</v-alert>
                </v-card-text>
                <v-card-text>
                    <v-form
                        @submit.prevent="
                            absenceForm.post(route('absence.store'), {
                                onSuccess: () => {
                                    absenceForm.reset();
                                    isActive.value = false;
                                },
                            })
                        "
                    >
                        <v-row>
                            <v-col cols="12">
                                <v-select
                                    label="Abwesenheitsgrund angeben"
                                    :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                                    v-model="absenceForm.absence_type_id"
                                    :error-messages="absenceForm.errors.absence_type_id"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    type="date"
                                    label="Von"
                                    v-model="absenceForm.start"
                                    :error-messages="absenceForm.errors.start"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    type="date"
                                    label="Bis"
                                    v-model="absenceForm.end"
                                    :error-messages="absenceForm.errors.end"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" class="text-end">
                                <v-btn :loading="absenceForm.processing" type="submit" color="primary">beantragen</v-btn>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
