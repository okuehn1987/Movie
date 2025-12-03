<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Notification, Paginator, User, UserAppends } from '@/types/types';
import { ref } from 'vue';
import NotificationTable from './partials/NotificationTable.vue';

defineProps<{
    archiveNotifications: Paginator<Notification>;
    flowNotifications: Paginator<Notification>;
    tideNotifications: Paginator<Notification>;
    triggeredByUsers: (User & UserAppends)[];
}>();

const currentTab = ref(route().params['tab'] || 'flow');
</script>
<template>
    <AdminLayout title="Benachrichtigungen">
        <v-card>
            <v-tabs v-model="currentTab">
                <v-tab value="flow" v-if="can('app', 'flow')">
                    Flow
                    <v-badge
                        v-if="flowNotifications.total > 0"
                        :content="flowNotifications.total <= 99 ? flowNotifications.total : '99+'"
                        color="error"
                        inline
                    ></v-badge>
                </v-tab>
                <v-tab value="tide" v-if="can('app', 'tide')">
                    Tide
                    <v-badge
                        v-if="tideNotifications.total > 0"
                        :content="tideNotifications.total <= 99 ? tideNotifications.total : '99+'"
                        color="error"
                        inline
                    ></v-badge>
                </v-tab>
                <v-tab value="archive">Archiv</v-tab>
            </v-tabs>
            <v-tabs-window :model-value="currentTab">
                <v-tabs-window-item value="flow" v-if="can('app', 'flow')">
                    <NotificationTable :triggeredByUsers :notifications="flowNotifications" tab="flow"></NotificationTable>
                </v-tabs-window-item>
                <v-tabs-window-item value="tide" v-if="can('app', 'tide')">
                    <NotificationTable :triggeredByUsers :notifications="tideNotifications" tab="tide"></NotificationTable>
                </v-tabs-window-item>
                <v-tabs-window-item value="archive">
                    <NotificationTable :triggeredByUsers :notifications="archiveNotifications" tab="archive"></NotificationTable>
                </v-tabs-window-item>
            </v-tabs-window>
        </v-card>
    </AdminLayout>
</template>
