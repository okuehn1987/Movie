<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { AbsenceType, User } from '@/types/types';
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
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation' | 'requires_approval'>[];
    holidays: Record<string, string> | null;
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
        );
});
const openEditCreateAbsenceModal = ref(false);
const openShowAbsenceModal = ref(false);
const selectedAbsence = ref<null | AbsenceProp | AbsencePatchProp>(null);
const selectedUser = ref<null | User['id']>(null);
const selectedDate = ref<DateTime | null>(null);
const selectedAbsenceUser = computed(() => props.users.find(u => u.id === selectedAbsence.value?.user_id));

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
                <div class="d-flex flex-wrap justify-center align-center">
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
                </div>
            </v-card-text>
            <v-divider></v-divider>
            <v-data-table-virtual
                fixed-header
                style="white-space: pre"
                :style="{ maxHeight: absenceTableHeight }"
                id="absence-table"
                :items="users.map(u => ({ ...u, name: u.last_name + ', ' + u.first_name }))"
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
