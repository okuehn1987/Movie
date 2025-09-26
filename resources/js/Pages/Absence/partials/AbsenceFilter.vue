<script setup lang="ts">
import { AbsenceType, Status, User, UserAbsenceFilter } from '@/types/types';
import { ref, watch } from 'vue';
import { UserProp } from '../utils';
import { useDisplay } from 'vuetify';

const props = defineProps<{
    user_absence_filters: UserAbsenceFilter[];
    users: UserProp[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval' | 'type'>[];
}>();

const FORM_DEFAULT = {
    set: null,
    selected_users: [],
    selected_absence_types: [],
    selected_statuses: ['created', 'accepted'] as Status[],
};

const display = useDisplay();

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

const singleFilterForm = defineModel<
    ReturnType<
        typeof useForm<{
            set: null | UserAbsenceFilter['id'];
            selected_users: User['id'][];
            selected_absence_types: AbsenceType['id'][];
            selected_statuses: Status[];
        }>
    >
>('singleFilterForm', { required: true });

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

watch(
    () => singleFilterForm.value.set,
    newValue => {
        if (newValue == null) return;
        const selectedFilter = props.user_absence_filters.find(f => f.id == newValue);
        if (!selectedFilter) return;

        const data = {
            set: selectedFilter.id,
            selected_users: selectedFilter.data.user_ids,
            selected_absence_types: selectedFilter.data.absence_type_ids,
            selected_statuses: selectedFilter.data.statuses,
        };
        singleFilterForm.value.defaults(data);
        singleFilterForm.value.reset();
    },
);

function submit() {
    if (typeof filterForm.value.set != 'string' && filterForm.value.set?.value) {
        filterForm.value.patch(route('userAbsenceFilter.update', { userAbsenceFilter: filterForm.value.set.value }));
    } else {
        filterForm.value.post(route('userAbsenceFilter.store'), {
            onSuccess: response => {
                const filters = response.props['user_absence_filters'] as UserAbsenceFilter[];

                const newFilter = filters[filters.length - 1];

                if (!newFilter) {
                    console.error('Neuer Filter nicht gefunden');
                    return;
                }

                let parsedData = { user_ids: [], absence_type_ids: [], statuses: [] };
                try {
                    parsedData = typeof newFilter.data === 'string' ? JSON.parse(newFilter.data) : newFilter.data;
                } catch (e) {
                    console.error('Fehler bei der Datenverarbeitung', e);
                }

                filterForm.value.set = { value: newFilter.id, title: newFilter.name };
                filterForm.value.defaults({
                    set: filterForm.value.set,
                    selected_users: parsedData.user_ids ?? [],
                    selected_absence_types: parsedData.absence_type_ids ?? [],
                    selected_statuses: parsedData.statuses ?? [],
                });
                filterForm.value.reset();
            },
        });
    }
}

function editFilter(filter: UserAbsenceFilter) {
    const set = { value: filter.id, title: filter.name };
    filterForm.value.defaults({
        set,
        selected_users: filter.data.user_ids ?? [],
        selected_absence_types: filter.data.absence_type_ids ?? [],
        selected_statuses: filter.data.statuses ?? [],
    });
    filterForm.value.set = set;
    filterForm.value.reset();
}

const tab = ref<'Einzelfilter' | 'Filtergruppen'>('Einzelfilter');
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
                <v-divider></v-divider>
                <v-card-text>
                    <v-tabs v-if="!display.smAndDown.value" v-model="tab" color="primary">
                        <v-tab text="Einzelfilter" value="Einzelfilter"></v-tab>
                        <v-tab text="Filtergruppen" value="Filtergruppen"></v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="tab" class="w-100">
                        <v-tabs-window-item value="Einzelfilter">
                            <v-form>
                                <v-row class="mt-2">
                                    <v-col cols="12">
                                        <v-select
                                            label="Filtergruppe auswählen"
                                            auto-select-first="exact"
                                            variant="underlined"
                                            :items="user_absence_filters.map(u => ({ value: u.id, title: u.name }))"
                                            v-model="singleFilterForm.set"
                                            :error-messages="singleFilterForm.errors.set"
                                            item-title="title"
                                            item-value="value"
                                            required
                                            clearable
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Nutzer"
                                            :items="users.map(u => ({ title: u.first_name + ' ' + u.last_name, value: u.id }))"
                                            v-model="singleFilterForm.selected_users"
                                            :error-messages="singleFilterForm.errors.selected_users"
                                            clearable
                                            chips
                                            multiple
                                            variant="underlined"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-select
                                            label="Abwesenheitsgrund"
                                            :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                                            v-model="singleFilterForm.selected_absence_types"
                                            :error-messages="singleFilterForm.errors.selected_absence_types"
                                            clearable
                                            chips
                                            multiple
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-select
                                            label="Abwesenheitsstatus"
                                            :items="[
                                                { title: 'Erstellt', value: 'created' },
                                                { title: 'Akzeptiert', value: 'accepted' },
                                                { title: 'Abgelehnt', value: 'declined' },
                                            ]"
                                            v-model="singleFilterForm.selected_statuses"
                                            :error-messages="singleFilterForm.errors.selected_statuses"
                                            clearable
                                            chips
                                            multiple
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <div class="d-flex justify-end">
                                            <v-btn
                                                class="me-2"
                                                color="primary"
                                                variant="flat"
                                                @click="singleFilterForm.defaults(FORM_DEFAULT).reset()"
                                            >
                                                Zurücksetzen
                                            </v-btn>
                                            <v-btn color="primary" variant="flat" @click="isActive.value = false">Anwenden</v-btn>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-form>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="Filtergruppen">
                            <v-form @submit.prevent="submit()">
                                <v-row class="mt-2">
                                    <v-col cols="12" md="6">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field
                                                    label="Gruppename"
                                                    :value="
                                                        typeof filterForm.set === 'object' && filterForm.set !== null
                                                            ? filterForm.set.title
                                                            : filterForm.set ?? ''
                                                    "
                                                    v-model="filterForm.set"
                                                    :error-messages="filterForm.errors.set"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
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
                                            </v-col>
                                            <v-col cols="12">
                                                <v-select
                                                    label="Abwesenheitsgrund"
                                                    :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                                                    v-model="filterForm.selected_absence_types"
                                                    :error-messages="filterForm.errors.selected_absence_types"
                                                    clearable
                                                    chips
                                                    multiple
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12">
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
                                            </v-col>
                                        </v-row>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-data-table-virtual
                                            style="height: 350px; overflow-y: auto"
                                            :headers="[
                                                { title: 'Gruppenübersicht', key: 'name' },
                                                { title: '', key: 'action', align: 'end' },
                                            ]"
                                            :items="user_absence_filters"
                                            no-data-text="Keine Filtergruppen vorhanden."
                                        >
                                            <template #item.name="{ item }">
                                                {{ item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name }}
                                            </template>
                                            <template #item.action="{ item }">
                                                <v-row>
                                                    <v-col class="text-end">
                                                        <v-btn
                                                            :size="display.smAndDown.value ? 'small' : undefined"
                                                            class="me-2"
                                                            variant="flat"
                                                            color="primary"
                                                            @click="editFilter(item)"
                                                        >
                                                            <v-icon>mdi-pencil</v-icon>
                                                        </v-btn>
                                                        <v-btn
                                                            :size="display.smAndDown.value ? 'small' : undefined"
                                                            variant="flat"
                                                            color="error"
                                                            @click="
                                                                filterForm.delete(
                                                                    route('userAbsenceFilter.destroy', { userAbsenceFilter: item.id }),
                                                                    {
                                                                        onSuccess: () => {
                                                                            filterForm.defaults(FORM_DEFAULT).reset();
                                                                        },
                                                                    },
                                                                )
                                                            "
                                                        >
                                                            <v-icon>mdi-delete</v-icon>
                                                        </v-btn>
                                                    </v-col>
                                                </v-row>
                                            </template>
                                        </v-data-table-virtual>
                                    </v-col>
                                    <v-col cols="12" class="text-end">
                                        <v-btn class="me-2" color="primary" variant="flat" @click="filterForm.defaults(FORM_DEFAULT).reset()">
                                            Zurücksetzen
                                        </v-btn>
                                        <v-btn :disabled="!filterForm.isDirty" color="primary" type="submit">
                                            {{ typeof filterForm.set == 'string' || filterForm.set == null ? 'Anlegen' : 'Bearbeiten' }}
                                        </v-btn>
                                    </v-col>
                                </v-row>
                            </v-form>
                        </v-tabs-window-item>
                    </v-tabs-window>
                </v-card-text>
            </v-card>
        </template>
    </v-dialog>
</template>
