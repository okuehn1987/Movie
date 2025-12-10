<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, AbsenceType, RelationPick, Status, User } from '@/types/types';
import { DateTime } from 'luxon';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { useMaxScrollHeight } from '@/utils';
import { ref } from 'vue';

defineProps<{
    user: User & {
        leaveDaysForYear: number;
        absences: (Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'user_id' | 'absence_type_id'> &
            RelationPick<'absence', 'absence_type', 'id' | 'name'> & {
                usedDays: Record<string, number>;
            })[];
    };
    usedLeaveDaysForYear: Record<string, Record<Exclude<Status, 'declined'>, number>>;
    absenceTypes: Pick<AbsenceType, 'id' | 'name'>[];
}>();

const open = ref([]);
const openYear = ref([]);
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="absences"></UserShowNavBar>
        <v-card :max-height="useMaxScrollHeight(48).value" class="overflow-auto">
            <v-card-text>
                <v-row>
                    <v-col v-if="user.absences.length === 0"><v-alert color="info">Keine Abwesenheiten vorhanden.</v-alert></v-col>
                    <v-col>
                        <v-expansion-panels elevation="1" multiple v-model="openYear">
                            <template v-for="(data, year) in usedLeaveDaysForYear" :key="year">
                                <v-expansion-panel :value="year">
                                    <v-expansion-panel-title>
                                        {{ year }}
                                    </v-expansion-panel-title>
                                    <v-expansion-panel-text>
                                        <v-expansion-panels elevation="1" multiple v-model="open">
                                            <template v-for="absenceType in absenceTypes" :key="year + '/' + absenceType.id">
                                                <v-expansion-panel
                                                    :value="year + '/' + absenceType.id"
                                                    v-if="
                                                        user.absences
                                                            .filter(
                                                                a =>
                                                                    a.absence_type_id == absenceType.id &&
                                                                    DateTime.fromSQL(a.start).year <= Number(year) &&
                                                                    DateTime.fromSQL(a.end).year >= Number(year),
                                                            )
                                                            .reduce((a, c) => a + (c.usedDays[year] ?? 0), 0) > 0
                                                    "
                                                >
                                                    <v-expansion-panel-title>
                                                        <v-row>
                                                            <v-col cols="12" md="11">
                                                                <h2 class="text-h6">{{ absenceType.name }}</h2>
                                                            </v-col>
                                                            <v-col cols="12" md="1" style="place-self: center">
                                                                {{
                                                                    user.absences
                                                                        .filter(
                                                                            a =>
                                                                                a.absence_type_id == absenceType.id &&
                                                                                DateTime.fromSQL(a.start).year <= Number(year) &&
                                                                                DateTime.fromSQL(a.end).year >= Number(year),
                                                                        )
                                                                        .reduce((a, c) => a + (c.usedDays[year] ?? 0), 0)
                                                                }}
                                                                Tage
                                                            </v-col>
                                                        </v-row>
                                                    </v-expansion-panel-title>
                                                    <v-expansion-panel-text>
                                                        <v-data-table-virtual
                                                            id="userAbsenceTable"
                                                            :items="
                                                                user.absences
                                                                    .filter(
                                                                        a =>
                                                                            a.absence_type_id == absenceType.id &&
                                                                            DateTime.fromSQL(a.start).year <= Number(year) &&
                                                                            DateTime.fromSQL(a.end).year >= Number(year),
                                                                    )
                                                                    .toSorted((a, b) => b.start.localeCompare(a.start))
                                                                    .map(e => ({
                                                                        ...e,
                                                                        start: DateTime.fromSQL(e.start).toFormat('dd.MM.yyyy'),
                                                                        end: DateTime.fromSQL(e.end).toFormat('dd.MM.yyyy'),
                                                                        absence_type: e.absence_type?.name,
                                                                        usedDays: e.usedDays[year],
                                                                    }))
                                                            "
                                                            :headers="[
                                                                { title: 'Start', key: 'start' },
                                                                { title: 'Ende', key: 'end' },
                                                                { title: 'Status', key: 'status' },
                                                                { title: 'Art', key: 'absence_type' },
                                                                { title: 'Genutzte Tage', key: 'usedDays' },
                                                            ]"
                                                        >
                                                            <template v-slot:item.status="{ item }">
                                                                <v-chip
                                                                    :color="
                                                                        { accepted: 'success', created: 'warning', declined: 'error' }[item.status]
                                                                    "
                                                                >
                                                                    {{
                                                                        { accepted: 'Genehmigt', created: 'Offen', declined: 'Abgelehnt' }[
                                                                            item.status
                                                                        ]
                                                                    }}
                                                                </v-chip>
                                                            </template>
                                                            <template v-slot:item.start="{ item }">
                                                                {{
                                                                    DateTime.fromFormat(item.start, 'dd.MM.yyyy').year != Number(year)
                                                                        ? '01.01.' + year
                                                                        : item.start
                                                                }}
                                                            </template>
                                                            <template v-slot:item.end="{ item }">
                                                                {{
                                                                    DateTime.fromFormat(item.end, 'dd.MM.yyyy').year != Number(year)
                                                                        ? '31.12.' + year
                                                                        : item.end
                                                                }}
                                                            </template>
                                                            <template v-slot:body.append v-if="absenceType.name == 'Urlaub'">
                                                                <tr class="font-weight-bold">
                                                                    <td colspan="4">
                                                                        Genehmigte Urlaubstage für das Jahr
                                                                        {{ DateTime.now().year }}:
                                                                    </td>
                                                                    <td>
                                                                        {{ data.accepted }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="font-weight-bold">
                                                                    <td colspan="4">
                                                                        Beantragte Urlaubstage für das Jahr
                                                                        {{ DateTime.now().year }}:
                                                                    </td>
                                                                    <td>
                                                                        {{ data.created }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="font-weight-bold">
                                                                    <td colspan="4">
                                                                        Gesamt genutzte Urlaubstage für das Jahr
                                                                        {{ DateTime.now().year }}:
                                                                    </td>
                                                                    <td>
                                                                        {{ data.accepted + data.created + ' von ' + user.leaveDaysForYear }}
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                        </v-data-table-virtual>
                                                    </v-expansion-panel-text>
                                                </v-expansion-panel>
                                            </template>
                                        </v-expansion-panels>
                                    </v-expansion-panel-text>
                                </v-expansion-panel>
                            </template>
                        </v-expansion-panels>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
