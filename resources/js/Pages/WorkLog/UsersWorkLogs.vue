<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { User, WorkLog } from '@/types/types';
import { DateTime } from 'luxon';

defineProps<{
    users: (Pick<User, 'first_name' | 'last_name'> & { latestWorkLog: WorkLog; isPresent: boolean })[];
}>();
</script>
<template>
    <AdminLayout title="Heutige Arbeitszeiten">
        <v-data-table-virtual
            :items="
                users.map(u => ({
                    ...u,
                    lastAction: u.latestWorkLog.end ? 'Gehen' : 'Kommen',
                    time: DateTime.fromSQL(u.latestWorkLog.end ? u.latestWorkLog.end : u.latestWorkLog.start).toFormat('dd.MM.yyyy HH:mm'),
                }))
            "
            :headers="[
                { title: '', key: 'isPresent' },
                { title: 'Vorname', key: 'first_name' },
                { title: 'Nachname', key: 'last_name' },
                { title: 'Letzte Buchung', key: 'lastAction' },
                { title: 'Uhrzeit', key: 'time' },
            ]"
        >
            <template v-slot:item.isPresent="{ item }">
                <v-icon icon="mdi-circle" :color="item.isPresent ? 'success' : 'error'" size="md"></v-icon>
            </template>
        </v-data-table-virtual>
    </AdminLayout>
</template>
