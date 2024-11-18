<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, User, UserWorkingWeek, Weekday } from '@/types/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import { ref } from 'vue';

type UserProp = Pick<User, 'id' | 'first_name' | 'last_name' | 'supervisor_id'> & {
    absences: (Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'absence_type_id'> & { absence_type?: Pick<AbsenceType, 'id' | 'abbreviation'> })[];
    user_working_weeks: Pick<UserWorkingWeek, 'id' | Weekday>[];
};

const props = defineProps<{
    users: UserProp[];
    absence_types: Pick<AbsenceType, 'id' | 'name'>[];
}>();

const page = usePage();

function getDaysInMonth() {
    const daysInMonth = [];
    for (let i = 1; i <= DateTime.now().daysInMonth; i++) {
        daysInMonth.push(
            DateTime.now()
                .startOf('month')
                .plus({ day: i - 1 }),
        );
    }
    return daysInMonth;
}

function getUserStatus(user: UserProp, day: DateTime) {
    const absence = user.absences.find(a => DateTime.fromSQL(a.start) <= day && day <= DateTime.fromSQL(a.end));
    if (absence && absence.status === 'accepted') return absence.absence_type?.abbreviation || '❌';

    if (user.user_working_weeks.find(e => e[day.setLocale('en-US').weekdayLong?.toLowerCase() as Weekday])) return ' ';

    return '';
}

function isUserEditable(user_id: User['id'], editable: Pick<User, 'id' | 'supervisor_id'>) {
    return editable.supervisor_id === user_id || editable.id == user_id;
}

const openModal = ref(false);
const absenceForm = useForm({
    absence_type_id: undefined as undefined | number,
    start: new Date(),
    end: new Date(),
    user_id: page.props.auth.user.id,
});

function createAbsenceModal(day: string, user_id: number) {
    const absentUser = props.users.find(u => u.id === user_id);
    if (!absentUser || !isUserEditable(page.props.auth.user.id, absentUser)) return;

    const dayInDaytime = DateTime.now().startOf('month').plus({ day: +day });
    const absenceToEdit = absentUser.absences.find(a => DateTime.fromSQL(a.start) <= dayInDaytime && dayInDaytime <= DateTime.fromSQL(a.end));
    if (absenceToEdit) absenceForm.absence_type_id = absenceToEdit.absence_type_id;

    absenceForm.user_id = user_id;
    absenceForm.start = DateTime.now()
        .startOf('month')
        .plus({ day: +day - 1 })
        .toJSDate();
    absenceForm.end = DateTime.now().startOf('month').plus({ day: +day }).toJSDate();

    openModal.value = true;
}
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
                                absenceForm
                                    .transform(data => ({
                                        ...data,
                                        start: data.start.toLocaleDateString(),
                                        end: data.end.toLocaleDateString(),
                                    }))
                                    .post(route('absence.store'), {
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
                                    <v-date-input label="Von" v-model="absenceForm.start" :error-messages="absenceForm.errors.start"></v-date-input>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-date-input label="Bis" v-model="absenceForm.end" :error-messages="absenceForm.errors.end"></v-date-input>
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

        <v-data-table-virtual
            style="white-space: pre"
            id="absence-table"
            :items="
                users.map(u => ({
                    ...u,
                    name: u.first_name + '\n' + u.last_name,
                    ...Object.fromEntries(getDaysInMonth().map(d => [d.day, getUserStatus(u, d)])),
                }))
            "
            :headers="[
                {
                    title: 'Name',
                    key: 'name',
                },
                ...getDaysInMonth().map(e => ({
                    title: e.weekdayShort + '\n' + e.day.toString(),
                    key: e.day.toString(),
                    sortable: false,
                    align: 'center',
                } as const)),
            ]"
        >
            <template v-slot:item="{ item, columns }">
                <tr>
                    <td
                        v-for="header in columns"
                        :key="header.key + ''"
                        :style="{ backgroundColor: !item[header.key as keyof typeof item] ? 'lightgray' : '' }"
                        :class="{ 'editable-cell': isUserEditable(page.props.auth.user.id, item) }"
                        :role="isUserEditable(page.props.auth.user.id, item) ? 'button' : 'cell'"
                        @click="createAbsenceModal(header.key + '', item.id)"
                    >
                        {{ item[header.key as keyof typeof item] }}
                    </td>
                </tr>
            </template>
        </v-data-table-virtual>
    </AdminLayout>
</template>
<style>
/* so that the table wont overflow on full hd for 31 days - the dates can get less padding  */
#absence-table tr *:not(:first-child) {
    padding-inline: 4px;
    text-align: center;
}
.editable-cell:not(:first-child):hover {
    background-color: darkgray !important;
}
</style>
