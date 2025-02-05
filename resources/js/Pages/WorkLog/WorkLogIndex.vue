<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { TimeAccount, User, WorkLog } from '@/types/types';
import { getMaxScrollHeight } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { DateTime, Duration } from 'luxon';

defineProps<{
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & { latest_work_log: WorkLog; default_time_account: TimeAccount })[];
}>();
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
                    formatted_timeAccountBalance: Duration.fromObject({ seconds: u.default_time_account.balance }).toFormat('h:mm'),
                    lastAction: u.latest_work_log.end ? 'Gehen' : 'Kommen',
                    time: DateTime.fromSQL(u.latest_work_log.end ? u.latest_work_log.end : u.latest_work_log.start).toFormat('dd.MM.yyyy HH:mm'),
                }))
            "
            :headers="[
                { title: '', key: 'isPresent', sortable: false },
                { title: 'Vorname', key: 'first_name' },
                { title: 'Nachname', key: 'last_name' },
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
                {{ item.formatted_timeAccountBalance }}
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
