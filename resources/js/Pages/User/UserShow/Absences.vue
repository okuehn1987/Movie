<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Absence, RelationPick, User } from '@/types/types';
import { DateTime } from 'luxon';
import UserShowNavBar from './partial/UserShowNavBar.vue';

defineProps<{
    user: User & {
        leaveDaysForYear: number;
        usedLeaveDaysForYear: number;
        absences: (Pick<Absence, 'id' | 'start' | 'end' | 'status' | 'user_id' | 'absence_type_id'> &
            RelationPick<'absence', 'absence_type', 'id' | 'name'> & {
                usedDays: number;
            })[];
    };
}>();
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="absences"></UserShowNavBar>
        <v-card>
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-data-table-virtual
                            id="userAbsenceTable"
                            :items="
                                user.absences.map(e => ({
                                    ...e,
                                    start: DateTime.fromSQL(e.start).toFormat('dd.MM.yyyy'),
                                    end: DateTime.fromSQL(e.end).toFormat('dd.MM.yyyy'),
                                    absence_type: e.absence_type?.name,
                                    usedDays: e.usedDays.toString(),
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
                                <v-chip :color="{ accepted: 'success', created: 'warning', declined: 'error' }[item.status]">
                                    {{ { accepted: 'Genehmigt', created: 'Offen', declined: 'Abgelehnt' }[item.status] }}
                                </v-chip>
                            </template>
                            <template v-slot:body.append>
                                <tr class="font-weight-bold">
                                    <td colspan="4">
                                        Genutzte Urlaubstage für das Jahr
                                        {{ DateTime.now().year }}:
                                    </td>
                                    <td>
                                        {{ user.usedLeaveDaysForYear + ' von ' + user.leaveDaysForYear }}
                                    </td>
                                </tr>
                            </template>
                        </v-data-table-virtual>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>
    </AdminLayout>
</template>
<style lang="scss" scoped></style>
