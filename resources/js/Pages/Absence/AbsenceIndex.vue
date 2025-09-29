<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, Status, User, UserAbsenceFilter } from '@/types/types';
import { throttle, useMaxScrollHeight } from '@/utils';
import { router } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { computed, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import AbsenceFilter from './partials/AbsenceFilter.vue';
import AbsenceTableCell from './partials/AbsenceTableCell.vue';
import EditCreateAbsence from './partials/EditCreateAbsence.vue';
import ShowAbsenceModal from './partials/ShowAbsenceModal.vue';
import { AbsencePatchProp, AbsenceProp, getEntryState, UserProp } from './utils';

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

const filterForm = useForm({
    set: null as null | string | { value: UserAbsenceFilter['id']; title: string },
    selected_users: [] as User['id'][],
    selected_absence_types: [] as AbsenceType['id'][],
    selected_statuses: ['created', 'accepted'] as Status[],
});

const singleFilterForm = useForm({
    set: null as null | UserAbsenceFilter['id'],
    selected_users: [] as User['id'][],
    selected_absence_types: [] as AbsenceType['id'][],
    selected_statuses: ['created', 'accepted'] as Status[],
});

const currentFilterForm = ref<null | typeof filterForm | typeof singleFilterForm>(null);

watch([() => singleFilterForm.set, () => filterForm.set], () => {
    if (filterForm.set == null) currentFilterForm.value = singleFilterForm;
    else currentFilterForm.value = filterForm;
});

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
        .filter(entry => {
            return (
                !currentFilterForm.value ||
                ((currentFilterForm.value.selected_users.length == 0 || currentFilterForm.value.selected_users.includes(entry.user_id)) &&
                    (currentFilterForm.value.selected_absence_types.length == 0 ||
                        !entry.absence_type_id ||
                        currentFilterForm.value.selected_absence_types.includes(entry.absence_type_id)) &&
                    (currentFilterForm.value.selected_statuses.length == 0 || currentFilterForm.value.selected_statuses.includes(entry.status)))
            );
        });
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

function getDaysInWeek() {
    const daysInWeek = [];
    for (let i = 1; i <= 7; i++) {
        daysInWeek.push(date.value.startOf('week').plus({ day: i - 1 }));
    }
    return daysInWeek;
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

const display = useDisplay();
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
                <div class="d-flex justify-space-between align-center w-100">
                    <AbsenceFilter
                        :absence_types
                        :users
                        :user_absence_filters
                        v-model:filterForm="filterForm"
                        v-model:singleFilterForm="singleFilterForm"
                    ></AbsenceFilter>
                    <div class="d-flex justify-center align-center w-100">
                        <div class="d-flex">
                            <template v-if="display.mdAndUp.value">
                                <v-btn @click.stop="date = date.minus({ year: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-double-left"></v-icon>
                                </v-btn>
                                <v-btn @click.stop="date = date.minus({ month: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-left"></v-icon>
                                </v-btn>
                            </template>
                            <v-btn v-else @click.stop="date = date.minus({ week: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-left"></v-icon>
                            </v-btn>
                        </div>
                        <h2 class="mx-md-4 text-center" :style="{ minWidth: display.mdAndUp.value ? '170px' : '110px' }">
                            <template v-if="display.smAndUp.value">{{ date.toFormat('MMMM yyyy') }}</template>
                            <template v-else>
                                {{ date.startOf('week').toFormat('dd.MM.yyyy') }} - {{ date.endOf('week').toFormat('dd.MM.yyyy') }}
                            </template>
                            <v-progress-linear v-if="loading" indeterminate></v-progress-linear>
                        </h2>
                        <div class="d-flex">
                            <template v-if="display.mdAndUp.value">
                                <v-btn @click.stop="date = date.plus({ month: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-right"></v-icon>
                                </v-btn>
                                <v-btn @click.stop="date = date.plus({ year: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-double-right"></v-icon>
                                </v-btn>
                            </template>
                            <v-btn v-else @click.stop="date = date.plus({ week: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-right"></v-icon>
                            </v-btn>
                        </div>
                    </div>
                    <div style="width: 64px" v-if="display.mdAndUp.value"></div>
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
                        .filter(
                            u =>
                                !currentFilterForm || currentFilterForm.selected_users.length == 0 || currentFilterForm.selected_users.includes(u.id),
                        )
                        .map(u => ({
                            ...u,
                            name: display.mdAndUp.value ? u.last_name + ', ' + u.first_name : u.first_name.substring(0, 1) + '.' + u.last_name,
                        }))
                        .toSorted((a, b) => a.last_name.localeCompare(b.last_name))
                "
                :headers="[
                    {
                        title: 'Name',
                        key: 'name',
                        headerProps: {class:'px-sm-4 px-1'}
                    },
                  ...( display.mdAndUp.value?  [  {
                            title: '',
                            key: 'action',
                            width: '48px',
                            sortable:false
                        }]: []),
                    ...(display.mdAndUp.value ? getDaysInMonth() :getDaysInWeek()).map(e => ({
                        title: e.weekdayShort + '\n' + e.day.toString(),
                        key: e.toString(),
                        sortable: false,
                        align: 'center',
                        width: display.mdAndUp.value?'44px': '14%' ,
                        headerProps:{ class: {'bg-blue-darken-2': e.toISODate() === DateTime.local().toISODate() }}
                    } as const)),
                ]"
            >
                <template v-slot:item="{ item, columns }">
                    <tr>
                        <template v-for="header in columns" :key="header.key">
                            <td
                                style="max-width: calc(100vw - 28px * 7); text-overflow: ellipsis; overflow: hidden; white-space: nowrap"
                                class="px-sm-4 px-1"
                                v-if="header.key === 'name'"
                            >
                                {{ item.name }}
                            </td>
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
                                @click="can('absence', 'create', item) && createAbsenceModal(item.id, DateTime.fromISO(header.key!))"
                                :holidays="holidays"
                                :absenceTypes="absence_types"
                                :user="item"
                                :date="DateTime.fromISO(header.key!)"
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
