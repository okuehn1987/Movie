<script setup lang="ts">
import { AbsenceType, DateString, FederalState, Group, OperatingSite, Status, User, UserAbsenceFilter } from '@/types/types';
import { ref, watch } from 'vue';
import { UserProp } from '../utils';
import { useDisplay } from 'vuetify';

const props = defineProps<{
    userAbsenceFilters: UserAbsenceFilter[];
    users: UserProp[];
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval' | 'type'>[];
    schoolHolidays: Record<string, { name: string; start: DateString; end: DateString }[]>;
    federal_state: FederalState;
    all_federal_states: Record<FederalState, string>;
    filterableOperatingSites: Pick<OperatingSite, 'id' | 'name'>[];
    filterableGroups: Pick<Group, 'id' | 'name'>[];
}>();

const FORM_DEFAULT = {
    set: null,
    selected_users: [],
    selected_absence_types: [],
    selected_statuses: ['created', 'accepted'] as Status[],
    selected_holidays: [props.federal_state] as FederalState[],
    selected_operating_sites: [],
    selected_groups: [],
};

const display = useDisplay();

const groupFilterForm = defineModel<
    ReturnType<
        typeof useForm<{
            set: null | string | { value: UserAbsenceFilter['id']; title: string };
            selected_users: User['id'][];
            selected_absence_types: AbsenceType['id'][];
            selected_statuses: Status[];
            selected_holidays: FederalState[];
            selected_operating_sites: OperatingSite['id'][];
            selected_groups: Group['id'][];
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
            selected_holidays: FederalState[];
            selected_operating_sites: OperatingSite['id'][];
            selected_groups: Group['id'][];
        }>
    >
>('singleFilterForm', { required: true });

const deleteFilterForm = useForm({
    filter_id: null as null | UserAbsenceFilter['id'],
});

watch(() => groupFilterForm.value.set, selectExistingFilter);

function selectExistingFilter(newValue: typeof groupFilterForm.value.set) {
    if (newValue == null) return;
    let selectedFilter;
    if (typeof newValue == 'object' && groupFilterForm.value.isDirty) selectedFilter = props.userAbsenceFilters.find(f => f.id == newValue.value);
    else if (typeof newValue == 'string') selectedFilter = props.userAbsenceFilters.find(f => f.name == newValue);
    if (!selectedFilter) return;

    const data = {
        set: typeof newValue == 'string' ? { title: newValue, value: selectedFilter.id } : newValue,
        selected_users: selectedFilter.data.user_ids,
        selected_absence_types: selectedFilter.data.absence_type_ids,
        selected_statuses: selectedFilter.data.statuses,
        selected_holidays: selectedFilter.data.holidays_from_federal_states,
        selected_groups: selectedFilter.data.group_ids,
        selected_operating_sites: selectedFilter.data.operating_site_ids,
    };
    groupFilterForm.value.defaults(data).reset();
}

watch(
    () => singleFilterForm.value.set,
    newValue => {
        if (newValue == null) return;
        const selectedFilter = props.userAbsenceFilters.find(f => f.id == newValue);
        if (!selectedFilter) return;
        singleFilterForm.value.defaults({
            set: selectedFilter.id,
            selected_users: selectedFilter.data.user_ids,
            selected_absence_types: selectedFilter.data.absence_type_ids,
            selected_statuses: selectedFilter.data.statuses,
            selected_holidays: selectedFilter.data.holidays_from_federal_states,
            selected_groups: selectedFilter.data.group_ids,
            selected_operating_sites: selectedFilter.data.operating_site_ids,
        });
        singleFilterForm.value.reset();
    },
);

function submit() {
    if (typeof groupFilterForm.value.set != 'string' && groupFilterForm.value.set?.value) {
        groupFilterForm.value.patch(route('userAbsenceFilter.update', { userAbsenceFilter: groupFilterForm.value.set.value }));
    } else {
        groupFilterForm.value.post(route('userAbsenceFilter.store'), {
            onSuccess: () => {
                selectExistingFilter(groupFilterForm.value.set);
            },
        });
    }
}

function editFilter(filter: UserAbsenceFilter) {
    const set = { value: filter.id, title: filter.name };
    groupFilterForm.value.defaults({
        set,
        selected_users: filter.data.user_ids ?? [],
        selected_absence_types: filter.data.absence_type_ids ?? [],
        selected_statuses: filter.data.statuses ?? [],
        selected_holidays: filter.data.holidays_from_federal_states ?? [],
        selected_groups: filter.data.group_ids ?? [],
        selected_operating_sites: filter.data.operating_site_ids ?? [],
    });
    groupFilterForm.value.set = set;
    groupFilterForm.value.reset();
}

const tab = ref<'singleFilter' | 'groupFilter'>('singleFilter');

watch([() => singleFilterForm.value.set, () => groupFilterForm.value.set], ([newSingleFilter, newGroupFilter], [oldSingleFilter, oldGroupFilter]) => {
    if (oldSingleFilter == null && newSingleFilter != null) {
        groupFilterForm.value.defaults(FORM_DEFAULT).reset();
    } else if (oldGroupFilter == null && newGroupFilter != null) {
        singleFilterForm.value.defaults(FORM_DEFAULT).reset();
    }
});
</script>
<template>
    <v-dialog max-width="1000">
        <template #activator="{ props: activatorProps }">
            <v-btn :class="{ 'w-100 rounded-0': !display.smAndUp.value }" v-bind="activatorProps" variant="flat" color="primary">
                <v-icon>mdi-filter</v-icon>
            </v-btn>
        </template>
        <template #default="{ isActive }">
            <v-card title="Abwesenheiten filtern">
                <template #append>
                    <v-btn
                        icon
                        variant="text"
                        @click="
                            isActive.value = false;
                            filterForm.reset();
                        "
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </template>
                <v-divider></v-divider>
                <v-card-text class="pt-0">
                    <v-tabs v-if="!display.smAndDown.value" v-model="tab" color="primary">
                        <v-tab text="Einzelfilter" value="singleFilter"></v-tab>
                        <v-tab text="Filtergruppen" value="groupFilter"></v-tab>
                    </v-tabs>
                    <v-tabs-window v-model="tab" class="w-100">
                        <v-tabs-window-item value="singleFilter">
                            <v-form>
                                <v-row class="mt-2">
                                    <v-col cols="12">
                                        <v-select
                                            label="Filtergruppe auswählen"
                                            auto-select-first="exact"
                                            variant="underlined"
                                            :items="userAbsenceFilters.map(u => ({ value: u.id, title: u.name }))"
                                            v-model="singleFilterForm.set"
                                            :error-messages="singleFilterForm.errors.set"
                                            required
                                            hide-details
                                            clearable
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Betriebsstätten"
                                            :items="filterableOperatingSites.map(o => ({ title: o.name, value: o.id }))"
                                            v-model="singleFilterForm.selected_operating_sites"
                                            :error-messages="singleFilterForm.errors.selected_operating_sites"
                                            clearable
                                            chips
                                            multiple
                                            variant="underlined"
                                            autocomplete="off"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Abteilungen"
                                            :items="filterableGroups.map(g => ({ title: g.name, value: g.id }))"
                                            v-model="singleFilterForm.selected_groups"
                                            :error-messages="singleFilterForm.errors.selected_groups"
                                            clearable
                                            chips
                                            multiple
                                            variant="underlined"
                                            autocomplete="off"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-autocomplete
                                            label="Nutzer"
                                            :items="users.map(u => ({ title: u.first_name + ' ' + u.last_name, value: u.id }))"
                                            v-model="singleFilterForm.selected_users"
                                            :error-messages="singleFilterForm.errors.selected_users"
                                            clearable
                                            chips
                                            hide-details
                                            multiple
                                            variant="underlined"
                                            autocomplete="off"
                                        ></v-autocomplete>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-select
                                            label="Abwesenheitsgrund"
                                            :items="absenceTypes.map(a => ({ title: a.name, value: a.id }))"
                                            v-model="singleFilterForm.selected_absence_types"
                                            :error-messages="singleFilterForm.errors.selected_absence_types"
                                            clearable
                                            chips
                                            hide-details
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
                                            hide-details
                                            multiple
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-select
                                            label="Ferien"
                                            clearable
                                            chips
                                            multiple
                                            v-model="singleFilterForm.selected_holidays"
                                            :error-messages="singleFilterForm.errors.selected_holidays"
                                            :items="
                                                Object.entries(all_federal_states).map(([key, value]) => ({
                                                    value: key,
                                                    title: value,
                                                }))
                                            "
                                        ></v-select>
                                    </v-col>
                                    <v-col cols="12">
                                        <div class="d-flex justify-end">
                                            <v-btn
                                                class="me-2"
                                                color="primary"
                                                variant="flat"
                                                @click.stop="singleFilterForm.defaults(FORM_DEFAULT).reset()"
                                            >
                                                Leeren
                                            </v-btn>
                                            <v-btn color="primary" variant="flat" @click.stop="isActive.value = false">Anwenden</v-btn>
                                        </div>
                                    </v-col>
                                </v-row>
                            </v-form>
                        </v-tabs-window-item>
                        <v-tabs-window-item value="groupFilter">
                            <v-form @submit.prevent="submit()">
                                <v-row class="mt-2">
                                    <v-col cols="12" md="6">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field
                                                    label="Gruppename"
                                                    :model-value="
                                                        typeof groupFilterForm.set === 'object' && groupFilterForm.set !== null
                                                            ? groupFilterForm.set.title
                                                            : (groupFilterForm.set ?? '')
                                                    "
                                                    @update:model-value="
                                                        newValue => {
                                                            groupFilterForm.set = newValue;
                                                        }
                                                    "
                                                    :error-messages="groupFilterForm.errors.set"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-autocomplete
                                                    label="Nutzer"
                                                    :items="users.map(u => ({ title: u.first_name + ' ' + u.last_name, value: u.id }))"
                                                    v-model="groupFilterForm.selected_users"
                                                    :error-messages="groupFilterForm.errors.selected_users"
                                                    clearable
                                                    chips
                                                    multiple
                                                    variant="underlined"
                                                    autocomplete="off"
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-autocomplete
                                                    label="Betriebsstätten"
                                                    :items="filterableOperatingSites.map(o => ({ title: o.name, value: o.id }))"
                                                    v-model="groupFilterForm.selected_operating_sites"
                                                    :error-messages="groupFilterForm.errors.selected_operating_sites"
                                                    clearable
                                                    chips
                                                    multiple
                                                    variant="underlined"
                                                    autocomplete="off"
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-autocomplete
                                                    label="Abteilungen"
                                                    :items="filterableGroups.map(g => ({ title: g.name, value: g.id }))"
                                                    v-model="groupFilterForm.selected_groups"
                                                    :error-messages="groupFilterForm.errors.selected_groups"
                                                    clearable
                                                    chips
                                                    multiple
                                                    variant="underlined"
                                                    autocomplete="off"
                                                ></v-autocomplete>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-select
                                                    label="Abwesenheitsgrund"
                                                    :items="absenceTypes.map(a => ({ title: a.name, value: a.id }))"
                                                    v-model="groupFilterForm.selected_absence_types"
                                                    :error-messages="groupFilterForm.errors.selected_absence_types"
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
                                                    v-model="groupFilterForm.selected_statuses"
                                                    :error-messages="groupFilterForm.errors.selected_statuses"
                                                    clearable
                                                    chips
                                                    multiple
                                                ></v-select>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-select
                                                    label="Ferien"
                                                    clearable
                                                    chips
                                                    multiple
                                                    v-model="groupFilterForm.selected_holidays"
                                                    :error-messages="groupFilterForm.errors.selected_holidays"
                                                    :items="
                                                        Object.entries(all_federal_states).map(([key, value]) => ({
                                                            value: key,
                                                            title: value,
                                                        }))
                                                    "
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
                                            :items="userAbsenceFilters"
                                            no-data-text="Keine Filtergruppen vorhanden."
                                        >
                                            <template #item.name="{ item }">
                                                {{ item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name }}
                                            </template>
                                            <template #item.action="{ item }">
                                                <v-row>
                                                    <v-col class="text-end">
                                                        <!-- TODO: Mobile visuality fixen -->
                                                        <v-btn
                                                            :size="display.smAndDown.value ? 'small' : undefined"
                                                            class="me-2"
                                                            variant="flat"
                                                            color="primary"
                                                            @click.stop="editFilter(item)"
                                                        >
                                                            <v-icon>mdi-pencil</v-icon>
                                                        </v-btn>
                                                        <v-btn
                                                            :size="display.smAndDown.value ? 'small' : undefined"
                                                            variant="flat"
                                                            color="error"
                                                            :loading="deleteFilterForm.processing && deleteFilterForm.filter_id == item.id"
                                                            @click.stop="
                                                                deleteFilterForm.filter_id = item.id;
                                                                deleteFilterForm.delete(
                                                                    route('userAbsenceFilter.destroy', { userAbsenceFilter: item.id }),
                                                                    {
                                                                        onSuccess: () => {
                                                                            groupFilterForm.defaults(FORM_DEFAULT).reset();
                                                                        },
                                                                    },
                                                                );
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
                                        <v-btn
                                            class="me-2"
                                            color="primary"
                                            variant="flat"
                                            @click.stop="groupFilterForm.defaults(FORM_DEFAULT).reset()"
                                        >
                                            Zurücksetzen
                                        </v-btn>
                                        <v-btn
                                            :disabled="!groupFilterForm.isDirty"
                                            color="primary"
                                            type="submit"
                                            :loading="groupFilterForm.processing"
                                        >
                                            {{ typeof groupFilterForm.set == 'string' || groupFilterForm.set == null ? 'Anlegen' : 'Bearbeiten' }}
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
