<script setup lang="ts">
import { AbsenceType, Status, User, UserAbsenceFilter } from '@/types/types';
import { watch } from 'vue';
import { UserProp } from '../utils';

const props = defineProps<{
    user_absence_filters: UserAbsenceFilter[];
    users: UserProp[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval' | 'type'>[];
}>();
const filterForm = defineModel<
    ReturnType<
        typeof useForm<{
            set: null | string | { value: UserAbsenceFilter['id']; title: string };
            selected_users: User['id'][];
            selected_absence_types: AbsenceType['id'][];
            selected_statuses: Status[];
        }>
    >
>('filterForm', { required: true });

watch(
    () => filterForm.value.set,
    newValue => {
        if (newValue == null) return;
        let selectedFilter;
        if (typeof newValue == 'object' && filterForm.value.isDirty) selectedFilter = props.user_absence_filters.find(f => f.id == newValue.value);
        else if (typeof newValue == 'string') selectedFilter = props.user_absence_filters.find(f => f.name == newValue);
        if (!selectedFilter) return;

        const data = {
            set: typeof newValue == 'string' ? { title: newValue, value: selectedFilter.id } : newValue,
            selected_users: selectedFilter.data.user_ids,
            selected_absence_types: selectedFilter.data.absence_type_ids,
            selected_statuses: selectedFilter.data.statuses,
        };
        filterForm.value.defaults(data);
        filterForm.value.reset();
    },
);

function submit() {
    if (typeof filterForm.value.set != 'string' && filterForm.value.set?.value) {
        filterForm.value.patch(route('userAbsenceFilter.update', { userAbsenceFilter: filterForm.value.set.value }));
    } else {
        filterForm.value.post(route('userAbsenceFilter.store'));
    }
}

function resetFilterForm() {
    const data = {
        set: '',
        selected_users: [],
        selected_absence_types: [],
        selected_statuses: ['created', 'accepted'] as Status[],
    };
    filterForm.value.defaults(data);
    filterForm.value.reset();
}
</script>
<template>
    <v-dialog max-width="1000">
        <template #activator="{ props: activatorProps }">
            <v-btn v-bind="activatorProps" variant="flat" color="primary"><v-icon>mdi-filter</v-icon></v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card>
                <template #title>
                    <div class="d-flex justify-space-between align-center w-100">
                        <span>Abwesenheiten filtern</span>
                    </div>
                </template>
                <template #append>
                    <v-btn icon variant="text" @click="isActive.value = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-card-text>
                    <v-form @submit.prevent="submit()">
                        <v-combobox
                            label="Filtergruppe auswählen oder neue erstellen"
                            auto-select-first="exact"
                            variant="underlined"
                            :items="user_absence_filters.map(u => ({ value: u.id, title: u.name }))"
                            v-model="filterForm.set"
                            :error-messages="filterForm.errors.set"
                            required
                            clearable
                        ></v-combobox>
                        <v-autocomplete
                            label="Nutzer"
                            :items="users.map(u => ({ title: u.first_name + ' ' + u.last_name, value: u.id }))"
                            v-model="filterForm.selected_users"
                            :error-messages="filterForm.errors.selected_users"
                            clearable
                            chips
                            multiple
                            variant="underlined"
                        ></v-autocomplete>
                        <v-select
                            label="Abwesenheitsgrund"
                            :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                            v-model="filterForm.selected_absence_types"
                            :error-messages="filterForm.errors.selected_absence_types"
                            clearable
                            chips
                            multiple
                        ></v-select>
                        <v-select
                            label="Abwesenheitsstatus"
                            :items="[
                                { title: 'Erstellt', value: 'created' },
                                { title: 'Akzeptiert', value: 'accepted' },
                                { title: 'Abgelehnt', value: 'declined' },
                            ]"
                            v-model="filterForm.selected_statuses"
                            :error-messages="filterForm.errors.selected_statuses"
                            clearable
                            chips
                            multiple
                        ></v-select>
                        <div class="d-flex justify-space-between">
                            <v-btn
                                v-if="typeof filterForm.set == 'object' && filterForm.set != null"
                                color="error"
                                @click="
                                    filterForm.delete(route('userAbsenceFilter.destroy', { userAbsenceFilter: filterForm.set.value }), {
                                        onSuccess: () => {
                                            resetFilterForm();
                                        },
                                    })
                                "
                            >
                                Filter löschen
                            </v-btn>
                            <div v-else></div>
                            <v-btn :disabled="!filterForm.isDirty" color="primary" type="submit">
                                {{ typeof filterForm.set == 'string' || filterForm.set == null ? 'Filter anlegen' : 'Filter bearbeiten' }}
                            </v-btn>
                        </div>
                    </v-form>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
