<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { User, WorkLog } from '@/types/types';
import { getMaxScrollHeight } from '@/utils';
import { Link } from '@inertiajs/vue3';
import { DateTime } from 'luxon';

defineProps<{
    users: (Pick<User, 'id' | 'first_name' | 'last_name'> & { latestWorkLog: WorkLog; isPresent: boolean })[];
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
                    lastAction: u.latestWorkLog.end ? 'Gehen' : 'Kommen',
                    time: DateTime.fromSQL(u.latestWorkLog.end ? u.latestWorkLog.end : u.latestWorkLog.start).toFormat('dd.MM.yyyy HH:mm'),
                }))
            "
            :headers="[
                { title: '', key: 'isPresent', sortable: false },
                { title: 'Vorname', key: 'first_name' },
                { title: 'Nachname', key: 'last_name' },
                { title: 'Letzte Buchung', key: 'lastAction' },
                { title: 'Uhrzeit', key: 'time' },
                { title: '', key: 'actions', sortable: false, align: 'end' },
            ]"
        >
            <template v-slot:item.isPresent="{ item }">
                <v-icon icon="mdi-circle" :color="item.isPresent ? 'success' : 'error'" size="md"></v-icon>
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
