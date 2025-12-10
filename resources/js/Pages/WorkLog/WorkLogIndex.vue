<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { RelationPick, Relations, User, WorkLog } from '@/types/types';
import { formatDuration, getMaxScrollHeight } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';

type PropUser = Pick<User, 'id' | 'first_name' | 'last_name' | 'time_balance_yellow_threshold' | 'time_balance_red_threshold'> &
    Pick<Relations<'user'>, 'default_time_account'> & { latest_work_log: WorkLog } & RelationPick<
        'user',
        'current_working_hours',
        'id' | 'weekly_working_hours'
    > &
    Pick<Relations<'user'>, 'current_working_week'>;

defineProps<{
    users: PropUser[];
}>();

function getPT(user: PropUser & { userWorkingWeekCount: number }) {
    if (!user.current_working_hours) return null;

    return (
        Math.round((user.default_time_account.balance / 3600 / (user.current_working_hours?.weekly_working_hours / user.userWorkingWeekCount)) * 10) /
        10
    );
}
</script>
<template>
    <AdminLayout title="Arbeitszeiten">
        <v-data-table-virtual
            :style="{ maxHeight: getMaxScrollHeight(0) }"
            fixed-header
            hover
            :items="
                users.map(u => ({
                    ...u,
                    timeAccountBalance: u.default_time_account.balance,
                    lastAction: u.latest_work_log.end ? 'Gehen' : 'Kommen',
                    time: DateTime.fromSQL(u.latest_work_log.end ? u.latest_work_log.end : u.latest_work_log.start).toFormat('dd.MM.yyyy HH:mm'),
                    timeBalanceTrafficLight:
                        u.time_balance_red_threshold !== null && u.time_balance_yellow_threshold !== null
                            ? Math.abs(u.default_time_account.balance) >= u.time_balance_red_threshold * 3600
                                ? 'error'
                                : Math.abs(u.default_time_account.balance) >= u.time_balance_yellow_threshold * 3600
                                ? 'warning'
                                : 'success'
                            : null,
                    userWorkingWeekCount: Object.entries(u.current_working_week ?? {}).filter(
                        ([key, value]) => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].includes(key) && value,
                    ).length,
                }))
            "
            :headers="[
                { title: '', key: 'isPresent', sortable: false },
                { title: 'Vorname', key: 'first_name' },
                { title: 'Nachname', key: 'last_name' },
                { title: '', key: 'timeBalanceTrafficLight', align: 'end', width: '1px' },
                { title: 'Gleitzeitkonto', key: 'timeAccountBalance' },
                { title: 'Letzte Buchung', key: 'lastAction' },
                { title: 'Uhrzeit', key: 'time' },
                { title: '', key: 'actions', sortable: false, align: 'end' },
            ]"
        >
            <template v-slot:item.isPresent="{ item }">
                <v-icon icon="mdi-circle" :color="item.latest_work_log.end ? 'error' : 'success'" size="md"></v-icon>
            </template>
            <template v-slot:item.timeAccountBalance="{ item }">
                {{ formatDuration(item.default_time_account.balance) }}
            </template>
            <template #item.timeBalanceTrafficLight="{ item }">
                <template v-if="item.timeBalanceTrafficLight">
                    <v-icon
                        icon="mdi-clock-time-three"
                        v-if="item.timeBalanceTrafficLight === 'success'"
                        color="success"
                        size="x-large"
                        :title="!!getPT(item) ? getPT(item) + ' PT' : '/'"
                    ></v-icon>
                    <v-icon
                        icon="mdi-clock-time-five"
                        v-if="item.timeBalanceTrafficLight === 'warning'"
                        color="warning"
                        size="x-large"
                        :title="!!getPT(item) ? getPT(item) + ' PT' : '/'"
                    ></v-icon>
                    <v-icon
                        icon="mdi-clock-time-nine"
                        v-if="item.timeBalanceTrafficLight === 'error'"
                        color="error"
                        size="x-large"
                        :title="!!getPT(item) ? getPT(item) + ' PT' : '/'"
                    ></v-icon>
                </template>
            </template>
            <template #item.actions="{ item }">
                <Link
                    :href="
                        route('user.workLog.index', {
                            user: item.id,
                            fromUserWorkLogs: true,
                        })
                    "
                >
                    <v-btn color="primary" variant="text" icon="mdi-eye" />
                </Link>
            </template>
        </v-data-table-virtual>
    </AdminLayout>
</template>
