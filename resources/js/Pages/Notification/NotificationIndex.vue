<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Paginator, User, UserAppends, Notification } from '@/types/types';
import { usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';

const props = defineProps<{
    notifications: Paginator<Notification>;
    triggeredByUsers: (User & UserAppends)[];
}>();

const { currentPage, lastPage, data } = usePagination(toRefs(props), 'notifications');
</script>
<template>
    <AdminLayout title="Benachrichtigungen">
        <v-card>
            <v-card-text>
                <v-data-table
                    v-model:page="currentPage"
                    :headers="[
                        { title: 'Titel', value: 'data.title' },
                        { title: 'Am', value: 'created_at' },
                        { title: 'Von', value: 'triggered_by' },
                    ]"
                    :items="
                        data.map(notification => ({
                            ...notification,
                            triggered_by: triggeredByUsers.find(user => user.id == notification.data.triggered_by)?.name || 'System',
                        }))
                    "
                >
                    <template v-slot:item.created_at="{ item }">
                        {{ DateTime.fromISO(item.created_at).toFormat('dd.MM.yyyy HH:mm') }}
                    </template>
                    <template v-slot:bottom>
                        <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
    </AdminLayout>
</template>
