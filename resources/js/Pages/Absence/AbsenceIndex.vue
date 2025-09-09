<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, Status, User, UserAbsenceFilter } from '@/types/types';
import { throttle, useMaxScrollHeight } from '@/utils';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed, ref, watch } from 'vue';
import AbsenceTableCell from './partials/AbsenceTableCell.vue';
import EditCreateAbsence from './partials/EditCreateAbsence.vue';
import { AbsencePatchProp, AbsenceProp, getEntryState, UserProp } from './utils';
import ShowAbsenceModal from './partials/ShowAbsenceModal.vue';

const props = defineProps<{
    users: UserProp[];
    absences: AbsenceProp[];
    absencePatches: AbsencePatchProp[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval' | 'type'>[];
    holidays: Record<string, string> | null;
    user_absence_filters: UserAbsenceFilter[];
}>();

const dateParam = route().params['date'];
const date = ref(dateParam ? (DateTime.fromFormat(dateParam, 'yyyy-MM') as DateTime<true>) : DateTime.now());

const currentEntries = computed(() => {
    const entries = [] as typeof props.absences | typeof props.absencePatches;
    return entries
        .concat(
            props.absencePatches.filter(
                a => a.start <= date.value.endOf('month').toFormat('yyyy-MM-dd') && a.end >= date.value.startOf('month').toFormat('yyyy-MM-dd'),
            ),
        )
        .concat(
            props.absences.filter(
                a => a.start <= date.value.endOf('month').toFormat('yyyy-MM-dd') && a.end >= date.value.startOf('month').toFormat('yyyy-MM-dd'),
            ),
        )
        .filter(
            entry =>
                (filterForm.selected_users.length == 0 || filterForm.selected_users.includes(entry.user_id)) &&
                (filterForm.selected_absence_types.length == 0 ||
                    !entry.absence_type_id ||
                    filterForm.selected_absence_types.includes(entry.absence_type_id)) &&
                (filterForm.selected_statuses.length == 0 || filterForm.selected_statuses.includes(entry.status)),
        );
});
const openEditCreateAbsenceModal = ref(false);
const openShowAbsenceModal = ref(false);
const selectedAbsence = ref<null | AbsenceProp | AbsencePatchProp>(null);
const selectedUser = ref<null | User['id']>(null);
const selectedDate = ref<DateTime | null>(null);
const selectedAbsenceUser = computed(() => props.users.find(u => u.id === selectedAbsence.value?.user_id));

const openAbsenceFromRoute = (() => {
    if (route().params['openAbsence']) {
        return props.absences?.find(absence => absence.id == Number(route().params['openAbsence'])) ?? null;
    }
    if (route().params['openAbsencePatch']) {
        return props.absencePatches?.find(absence => absence.id == Number(route().params['openAbsencePatch'])) ?? null;
    } else return null;
})();

if (openAbsenceFromRoute) {
    selectedAbsence.value = openAbsenceFromRoute;
    selectedUser.value = openAbsenceFromRoute.user_id;
    openEditCreateAbsenceModal.value = true;
}

const filterForm = useForm({
    set: null as null | string | { value: UserAbsenceFilter['id']; title: string },
    selected_users: [] as User['id'][],
    selected_absence_types: [] as AbsenceType['id'][],
    selected_statuses: ['created', 'accepted'] as Status[],
});

watch(
    () => filterForm.set,
    newValue => {
        if (newValue == null) return;
        let selectedFilter;
        if (typeof newValue == 'object' && filterForm.isDirty) selectedFilter = props.user_absence_filters.find(f => f.id == newValue.value);
        else if (typeof newValue == 'string') selectedFilter = props.user_absence_filters.find(f => f.name == newValue);
        if (!selectedFilter) return;

        const data = {
            set: typeof newValue == 'string' ? { title: newValue, value: selectedFilter.id } : newValue,
            selected_users: selectedFilter.data.user_ids,
            selected_absence_types: selectedFilter.data.absence_type_ids,
            selected_statuses: selectedFilter.data.statuses,
        };
        filterForm.defaults(data);
        filterForm.reset();
    },
);

function createAbsenceModal(user_id: User['id'], start?: DateTime) {
    selectedUser.value = user_id;
    const absenceToEdit = currentEntries.value
        .filter(a => a.user_id === user_id)
        .find(a => start && DateTime.fromSQL(a.start) <= start && start <= DateTime.fromSQL(a.end));

    selectedDate.value = start ?? null;
    selectedAbsence.value = absenceToEdit ?? null;
    if (absenceToEdit && ['hasOpenPatch', 'created'].includes(getEntryState(absenceToEdit))) {
        openShowAbsenceModal.value = true;
    } else {
        openEditCreateAbsenceModal.value = true;
    }
}

function getDaysInMonth() {
    const daysInMonth = [];
    for (let i = 1; i <= date.value.daysInMonth; i++) {
        daysInMonth.push(date.value.startOf('month').plus({ day: i - 1 }));
    }
    return daysInMonth;
}

const loadedMonths = ref([date.value.toFormat('yyyy-MM')]);
const loading = ref(false);
const reload = throttle(() => {
    if (loadedMonths.value.includes(date.value.toFormat('yyyy-MM'))) return;
    router.reload({
        only: ['absences', 'holidays'],
        data: { date: date.value.toFormat('yyyy-MM') },
        onStart: () => {
            loadedMonths.value.push(date.value.toFormat('yyyy-MM'));
            loading.value = true;
        },
        onError: () => (loadedMonths.value = loadedMonths.value.filter(e => e != date.value.toFormat('yyyy-MM'))),
        onFinish: () => (loading.value = false),
    });
}, 500);
watch(date, reload);

const absenceTableHeight = useMaxScrollHeight(80 + 1);

function submit() {
    if (typeof filterForm.set != 'string' && filterForm.set?.value) {
        filterForm.patch(route('userAbsenceFilter.update', { userAbsenceFilter: filterForm.set.value }));
    } else {
        filterForm.post(route('userAbsenceFilter.store'));
    }
}

function resetFilterForm() {
    const data = {
        set: '',
        selected_users: [],
        selected_absence_types: [],
        selected_statuses: ['created', 'accepted'] as Status[],
    };
    filterForm.defaults(data);
    filterForm.reset();
}
</script>
<template>
    <AdminLayout title="Abwesenheiten">
        <EditCreateAbsence
            v-if="openEditCreateAbsenceModal && selectedUser"
            :absence_types
            :users
            :selectedAbsence
            :selectedDate
            v-model:selectedUser="selectedUser"
            v-model="openEditCreateAbsenceModal"
        ></EditCreateAbsence>
        <ShowAbsenceModal
            v-if="openShowAbsenceModal && selectedAbsence && selectedAbsenceUser"
            :absence_types
            :users
            :selectedAbsence
            :absenceUser="selectedAbsenceUser"
            v-model="openShowAbsenceModal"
        ></ShowAbsenceModal>
        <v-card>
            <v-card-text>
                <div class="d-flex flex-wrap justify-space-between align-center">
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
                                    <!-- TODO: Error-messages missing -->
                                    <v-form @submit.prevent="submit()">
                                        <v-combobox
                                            label="Auswahl Filtergruppe"
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
                                            clearable
                                            chips
                                            multiple
                                            variant="underlined"
                                        ></v-autocomplete>
                                        <v-select
                                            label="Abwesenheitsgrund"
                                            :items="absence_types.map(a => ({ title: a.name, value: a.id }))"
                                            v-model="filterForm.selected_absence_types"
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
                                            clearable
                                            chips
                                            multiple
                                        ></v-select>
                                        <div class="d-flex justify-space-between">
                                            <v-btn
                                                v-if="typeof filterForm.set == 'object' && filterForm.set != null"
                                                color="error"
                                                @click="
                                                    filterForm.delete(
                                                        route('userAbsenceFilter.destroy', { userAbsenceFilter: filterForm.set.value }),
                                                        {
                                                            onSuccess: () => {
                                                                resetFilterForm();
                                                            },
                                                        },
                                                    )
                                                "
                                            >
                                                Filter löschen
                                            </v-btn>
                                            <div v-else></div>
                                            <v-btn :disabled="!filterForm.isDirty" color="primary" type="submit">
                                                {{ typeof filterForm.set == 'string' ? 'Filter anlegen' : 'Filter bearbeiten' }}
                                            </v-btn>
                                        </div>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </template>
                    </v-dialog>
                    <div class="d-flex flex-wrap align-center">
                        <div class="d-flex">
                            <v-btn @click.stop="date = date.minus({ year: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-double-left"></v-icon>
                            </v-btn>
                            <v-btn @click.stop="date = date.minus({ month: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-left"></v-icon>
                            </v-btn>
                        </div>
                        <h2 class="mx-4 text-center" style="min-width: 170px">
                            {{ date.toFormat('MMMM yyyy') }}
                            <v-progress-linear v-if="loading" indeterminate></v-progress-linear>
                        </h2>
                        <div class="d-flex">
                            <v-btn @click.stop="date = date.plus({ month: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-right"></v-icon>
                            </v-btn>
                            <v-btn @click.stop="date = date.plus({ year: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-double-right"></v-icon>
                            </v-btn>
                        </div>
                    </div>
                    <div style="width: 64px"></div>
                </div>
            </v-card-text>
            <v-divider></v-divider>
            <v-data-table-virtual
                fixed-header
                style="white-space: pre"
                :style="{ maxHeight: absenceTableHeight }"
                id="absence-table"
                :items="
                    users
                        .filter(u => filterForm.selected_users.length == 0 || filterForm.selected_users.includes(u.id))
                        .map(u => ({ ...u, name: u.last_name + ', ' + u.first_name }))
                "
                :headers="[
                {
                    title: 'Name',
                    key: 'name',
                },
                    {
                        title: '',
                        key: 'action',
                        width: '48px',
                        sortable:false
                    }, 
                ...getDaysInMonth().map(e => ({
                    title: e.weekdayShort + '\n' + e.day.toString(),
                    key: e.day.toString(),
                    sortable: false,
                    align: 'center',
                    headerProps:{ class: {'bg-blue-darken-2': e.toISODate() === DateTime.local().toISODate() }}
                } as const)),
            ]"
            >
                <template v-slot:item="{ item, columns }">
                    <tr>
                        <template v-for="header in columns" :key="header.key">
                            <td v-if="header.key === 'name'">{{ item.first_name }} {{ item.last_name }}</td>
                            <template v-else-if="header.key === 'action'">
                                <td v-if="can('absence', 'create', item)">
                                    <v-btn
                                        icon="mdi-plus"
                                        variant="text"
                                        @click="can('absence', 'create', item) && createAbsenceModal(item.id)"
                                    ></v-btn>
                                </td>
                                <td v-else></td>
                            </template>
                            <AbsenceTableCell
                                v-else
                                @click="
                                    can('absence', 'create', item) &&
                                        createAbsenceModal(item.id, date.startOf('month').plus({ day: +(header.key ?? 0) - 1 }))
                                "
                                :holidays="holidays"
                                :absenceTypes="absence_types"
                                :user="item"
                                :date="date.startOf('month').plus({ day: +(header.key ?? 0) - 1 })"
                                :entries="currentEntries.filter(a => a.user_id === item.id)"
                            ></AbsenceTableCell>
                        </template>
                    </tr>
                </template>
            </v-data-table-virtual>
        </v-card>
    </AdminLayout>
</template>
<style>
/* so that the table wont overflow on full hd for 31 days - the dates can get less padding  */
#absence-table tr *:not(:first-child) {
    padding-inline: 4px;
    text-align: center;
}
#absence-table thead tr {
    background-color: rgba(var(--v-theme-surface));
}
</style>
