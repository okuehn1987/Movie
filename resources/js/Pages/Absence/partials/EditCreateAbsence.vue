<script setup lang="ts">
import { AbsenceType, User } from '@/types/types';
import { DateTime } from 'luxon';
import { AbsencePatchProp, AbsenceProp, UserProp } from '../utils';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    users: UserProp[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval'>[];
    selectedAbsence: AbsenceProp | AbsencePatchProp | null;
    selectedDate: DateTime | null;
}>();

const openModal = defineModel<boolean>({ required: true });
const selectedUser = defineModel<User['id']>('selectedUser', { required: true });
const currentUser = computed(() => props.users.find(u => u.id === selectedUser.value));

const absenceForm = useForm({
    user_id: props.selectedAbsence?.user_id ?? selectedUser.value,
    start: props.selectedAbsence?.start ?? props.selectedDate?.toFormat('yyyy-MM-dd') ?? null,
    end: props.selectedAbsence?.end ?? props.selectedDate?.toFormat('yyyy-MM-dd') ?? null,
    absence_type_id: props.selectedAbsence?.absence_type_id ?? null,
    absence_id: props.selectedAbsence && 'absence_id' in props.selectedAbsence ? props.selectedAbsence.absence_id : props.selectedAbsence?.id ?? null,
});

function saveAbsence() {
    if (absenceForm.absence_id) {
        absenceForm.post(route('absence.absencePatch.store', { absence: absenceForm.absence_id }), {
            onSuccess: () => {
                absenceForm.reset();
                openModal.value = false;
            },
        });
    } else {
        absenceForm.post(route('absence.store'), {
            onSuccess: () => {
                absenceForm.reset();
                openModal.value = false;
            },
        });
    }
}
const deleteAbsenceForm = useForm({});
function deleteAbsence() {
    if (!props.selectedAbsence) return;
    deleteAbsenceForm.delete(
        route('absence.destroy', {
            absence: 'absence_id' in props.selectedAbsence ? props.selectedAbsence.absence_id : props.selectedAbsence.id,
        }),
        {
            onSuccess: () => {
                openModal.value = false;
            },
        },
    );
}

const requiresApproval = computed(() => {
    const type = props.absence_types.find(a => a.id === absenceForm.absence_type_id);
    return !props.selectedAbsence || (type?.requires_approval && usePage().props.auth.user.supervisor_id);
});
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
                    (can(
                        'absence',
                        'update',
                        users.find(u => u.id == absenceForm.user_id),
                    ) || !requiresApproval
                        ? ' bearbeiten'
                        : ' beantragen')
                "
            >
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            absenceForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text v-if="absenceForm.errors.user_id">
                    <v-alert color="error" closable>{{ absenceForm.errors.user_id }}</v-alert>
                </v-card-text>
                <v-card-text>
                    <v-form @submit.prevent="saveAbsence">
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
                            <v-alert v-if="currentUser && currentUser.leaveDaysForYear" type="info" class="w-100">
                                <v-row>
                                    <v-col cols="12" md="6">
                                        <div>Bereits verwendete Urlaubstage für {{ DateTime.now().year }}</div>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <div>
                                            {{ currentUser.usedLeaveDaysForYear ?? '0' }} von
                                            {{ currentUser.leaveDaysForYear }}
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-alert>
                            <v-col cols="12">
                                <div class="d-flex ga-2 justify-end">
                                    <template v-if="selectedAbsence">
                                        <v-btn
                                            v-if="
                                                can(
                                                    'absence',
                                                    'update',
                                                    users.find(u => u.id == absenceForm.user_id),
                                                )
                                            "
                                            @click.stop="deleteAbsence()"
                                            color="error"
                                            :loading="deleteAbsenceForm.processing"
                                        >
                                            löschen
                                        </v-btn>
                                        <v-btn v-else @click.stop="deleteAbsence()" color="error">Löschung beantragen</v-btn>
                                    </template>
                                    <v-btn :loading="absenceForm.processing" type="submit" color="primary" :disabled="!absenceForm.isDirty">
                                        {{
                                            can(
                                                'absence',
                                                'update',
                                                users.find(u => u.id == absenceForm.user_id),
                                            ) || !requiresApproval
                                                ? 'Speichern'
                                                : 'beantragen'
                                        }}
                                    </v-btn>
                                </div>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
<style lang="scss" scoped></style>
