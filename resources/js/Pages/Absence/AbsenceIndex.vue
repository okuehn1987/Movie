<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, DateString, HomeOfficeDay, Status, User, UserAbsenceFilter } from '@/types/types';
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
    absenceTypes: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval' | 'type'>[];
    holidays: Record<string, string> | null;
    date: DateString;
    userAbsenceFilters: UserAbsenceFilter[];
    homeOfficeDays: Pick<HomeOfficeDay, 'id' | 'user_id' | 'date' | 'status'>[];
}>();

const dateParam = route().params['date'];
const currentDate = ref(dateParam ? (DateTime.fromFormat(dateParam, 'yyyy-MM') as DateTime<true>) : DateTime.now());

const groupFilterForm = useForm({
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

const currentFilterForm = ref<null | typeof groupFilterForm | typeof singleFilterForm>(null);

watch(
    [() => singleFilterForm.data(), () => groupFilterForm.set],
    () => {
        if (groupFilterForm.set == null) currentFilterForm.value = singleFilterForm;
        else currentFilterForm.value = groupFilterForm;
    },
    { deep: true },
);

const currentMonthHomeOfficeDays = computed(() =>
    props.homeOfficeDays.filter(
        h => h.date >= currentDate.value.startOf('month').toFormat('yyyy-MM-dd') && h.date <= currentDate.value.endOf('month').toFormat('yyyy-MM-dd'),
    ),
);

const currentMonthEntries = computed(() => {
    const entries = [] as typeof props.absences | typeof props.absencePatches;
    return entries
        .concat(
            props.absencePatches.filter(
                a =>
                    a.start <= currentDate.value.endOf('month').toFormat('yyyy-MM-dd') &&
                    a.end >= currentDate.value.startOf('month').toFormat('yyyy-MM-dd'),
            ),
        )
        .concat(
            props.absences.filter(
                a =>
                    a.start <= currentDate.value.endOf('month').toFormat('yyyy-MM-dd') &&
                    a.end >= currentDate.value.startOf('month').toFormat('yyyy-MM-dd'),
            ),
        );
});

const currentEntries = computed(() => {
    return currentMonthEntries.value.filter(
        entry =>
            !currentFilterForm.value ||
            ((currentFilterForm.value.selected_users.length == 0 || currentFilterForm.value.selected_users.includes(entry.user_id)) &&
                (currentFilterForm.value.selected_absence_types.length == 0 ||
                    !entry.absence_type_id ||
                    currentFilterForm.value.selected_absence_types.includes(entry.absence_type_id)) &&
                (currentFilterForm.value.selected_statuses.length == 0 || currentFilterForm.value.selected_statuses.includes(entry.status))),
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
    for (let i = 1; i <= currentDate.value.daysInMonth; i++) {
        daysInMonth.push(currentDate.value.startOf('month').plus({ day: i - 1 }));
    }
    return daysInMonth;
}

function getDaysInWeek() {
    const daysInWeek = [];
    for (let i = 1; i <= 7; i++) {
        daysInWeek.push(currentDate.value.startOf('week').plus({ day: i - 1 }));
    }
    return daysInWeek;
}

const loadedMonths = ref([currentDate.value.toFormat('yyyy-MM')]);
const loading = ref(false);
const reload = throttle(() => {
    if (loadedMonths.value.includes(currentDate.value.toFormat('yyyy-MM'))) return;
    router.reload({
        only: ['absences', 'absencePatches', 'holidays'],
        data: { date: currentDate.value.toFormat('yyyy-MM'), openAbsence: null, openAbsencePatch: null },
        onStart: () => {
            loadedMonths.value.push(currentDate.value.toFormat('yyyy-MM'));
            loading.value = true;
        },
        onError: () => (loadedMonths.value = loadedMonths.value.filter(e => e != currentDate.value.toFormat('yyyy-MM'))),
        onFinish: () => (loading.value = false),
    });
}, 500);
watch(currentDate, reload);

const absenceTableHeight = useMaxScrollHeight(80 + 1);

const display = useDisplay();
</script>
<template>
    <AdminLayout title="Abwesenheiten">
        <EditCreateAbsence
            v-if="openEditCreateAbsenceModal && selectedUser"
            :absenceTypes
            :users
            :selectedAbsence
            :selectedDate
            v-model:selectedUser="selectedUser"
            v-model="openEditCreateAbsenceModal"
            @absenceReload="loadedMonths = [currentDate.toFormat('yyyy-MM')]"
        ></EditCreateAbsence>
        <ShowAbsenceModal
            v-if="openShowAbsenceModal && selectedAbsence && selectedAbsenceUser"
            :absenceTypes
            :users
            :selectedAbsence
            :absenceUser="selectedAbsenceUser"
            v-model="openShowAbsenceModal"
            @absenceReload="loadedMonths = [currentDate.toFormat('yyyy-MM')]"
        ></ShowAbsenceModal>
        <v-card>
            <v-card-text class="px-sm-4 px-0">
                <div class="d-flex align-center w-100" :class="display.mdAndUp.value ? 'justify-space-between' : 'justify-center'">
                    <AbsenceFilter
                        :absenceTypes
                        :users
                        :userAbsenceFilters
                        v-model:filterForm="groupFilterForm"
                        v-model:singleFilterForm="singleFilterForm"
                    ></AbsenceFilter>
                    <div class="d-flex flex-wrap align-center">
                        <div class="d-flex">
                            <template v-if="display.mdAndUp.value">
                                <v-btn @click.stop="currentDate = currentDate.minus({ year: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-double-left"></v-icon>
                                </v-btn>
                                <v-btn @click.stop="currentDate = currentDate.minus({ month: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-left"></v-icon>
                                </v-btn>
                            </template>
                            <v-btn v-else @click.stop="currentDate = currentDate.minus({ week: 1 })" variant="text" icon color="primary">
                                <v-icon icon="mdi-chevron-left"></v-icon>
                            </v-btn>
                        </div>
                        <h2 class="mx-md-4 text-center" :style="{ minWidth: display.mdAndUp.value ? '170px' : '110px' }">
                            <template v-if="display.mdAndUp.value">{{ currentDate.toFormat('MMMM yyyy') }}</template>
                            <template v-else>
                                {{ currentDate.startOf('week').toFormat('dd.MM.yy') }} - {{ currentDate.endOf('week').toFormat('dd.MM.yy') }}
                            </template>
                            <v-progress-linear v-if="loading" indeterminate></v-progress-linear>
                        </h2>
                        <div class="d-flex">
                            <template v-if="display.mdAndUp.value">
                                <v-btn @click.stop="currentDate = currentDate.plus({ month: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-right"></v-icon>
                                </v-btn>
                                <v-btn @click.stop="currentDate = currentDate.plus({ year: 1 })" variant="text" icon color="primary">
                                    <v-icon icon="mdi-chevron-double-right"></v-icon>
                                </v-btn>
                            </template>
                            <v-btn v-else @click.stop="currentDate = currentDate.plus({ week: 1 })" variant="text" icon color="primary">
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
                        .toSorted((a, b) =>
                            b.id == $page.props.auth.user.id ? 1 : a.id == $page.props.auth.user.id ? -1 : a.last_name.localeCompare(b.last_name),
                        )
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
                    ...(display.mdAndUp.value ? getDaysInMonth() : getDaysInWeek()).map((e,_,dayList) => ({
                        title: e.weekdayShort + '\n' + e.day.toString(),
                        key: e.toString(),
                        sortable: false,
                        align: 'center',
                        width: (1/dayList.length * 100)+'%' ,
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
                                :absenceTypes="absenceTypes"
                                :user="item"
                                :date="DateTime.fromISO(header.key!)"
                                :entries="currentEntries.filter(a => a.user_id === item.id)"
                                :homeOfficeDays="currentMonthHomeOfficeDays.filter(u => u.user_id === item.id)"
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
