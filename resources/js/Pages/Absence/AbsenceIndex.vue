<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, Canable, User, UserWorkingWeek, Weekday } from '@/types/types';
import { getMaxScrollHeight, throttle, usePageIsLoading } from '@/utils';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref, watch } from 'vue';
import AbsenceTableCell from './partials/AbsenceTableCell.vue';

type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'> &
    Canable & {
        user_working_weeks: Pick<UserWorkingWeek, 'id' | Weekday>[];
    };

const props = defineProps<{
    users: UserProp[];
    absences: Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'absence_type_id' | 'user_id'>[];
    absence_types: Pick<AbsenceType, 'id' | 'name' | 'abbreviation'>[];
    holidays: Record<string, string> | null;
}>();

const page = usePage();
const dateParam = route().params['date'];
const date = ref(dateParam ? (DateTime.fromFormat(dateParam, 'yyyy-MM') as DateTime<true>) : DateTime.now());

function getDaysInMonth() {
    const daysInMonth = [];
    for (let i = 1; i <= date.value.daysInMonth; i++) {
        daysInMonth.push(date.value.startOf('month').plus({ day: i - 1 }));
    }
    return daysInMonth;
}

const openModal = ref(false);
const absenceForm = useForm({
    user_id: page.props.auth.user.id,
    start: null as string | null,
    end: null as string | null,
    absence_type_id: null as null | AbsenceType['id'],
});

function createAbsenceModal(user_id: User['id'], start?: DateTime) {
    absenceForm.reset();
    const absenceToEdit = props.absences
        .filter(a => a.user_id === user_id)
        .find(a => start && DateTime.fromSQL(a.start) <= start && start <= DateTime.fromSQL(a.end));

    absenceForm.user_id = user_id;
    absenceForm.start = (start ?? date.value.startOf('month')).toFormat('yyyy-MM-dd');
    absenceForm.end = (start ?? date.value.startOf('month')).toFormat('yyyy-MM-dd');
    absenceForm.absence_type_id = absenceToEdit?.absence_type_id ?? null;

    openModal.value = true;
}

const loadedMonths = ref([date.value.toFormat('yyyy-MM')]);

const reload = throttle(() => {
    if (loadedMonths.value.includes(date.value.toFormat('yyyy-MM'))) return;
    router.reload({
        only: ['absences', 'holidays'],
        data: { date: date.value.toFormat('yyyy-MM') },
        onStart: () => loadedMonths.value.push(date.value.toFormat('yyyy-MM')),
        onError: () => (loadedMonths.value = loadedMonths.value.filter(e => e != date.value.toFormat('yyyy-MM'))),
    });
}, 500);
watch(date, reload);

const loading = usePageIsLoading();
</script>
<template>
    <AdminLayout title="Abwesenheiten">
        <v-dialog max-width="1000" v-model="openModal">
            <template #default="{ isActive }">
                <v-card
                    :title="
                        'Abwesenheit' +
                        (absenceForm.user_id != page.props.auth.user.id
                            ? ' von ' +
                              users.map(u => ({ ...u, name: u.first_name + ' ' + u.last_name })).find(u => u.id === absenceForm.user_id)?.name
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
                :style="{ maxHeight: getMaxScrollHeight(80 + 1) }"
                id="absence-table"
                :items="users"
                :headers="[
                {
                    title: 'Name',
                    key: 'name',
                },
                    {
                        title: '',
                        key: 'action',
                        width: '48px',
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
                                :absences="absences.filter(a => a.user_id === item.id)"
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
</style>
