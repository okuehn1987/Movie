<script setup lang="ts">
import { Notification, Paginator, User, UserAppends } from '@/types/types';
import { getNotificationUrl, usePagination } from '@/utils';
import { DateTime } from 'luxon';
import { toRefs } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    notifications: Paginator<Notification>;
    triggeredByUsers: (User & UserAppends)[];
    tab: 'flow' | 'tide' | 'archive';
}>();

const { currentPage, lastPage, data } = usePagination(
    toRefs(props),
    'notifications',
    { tab: props.tab },
    { flow: 'flowNotifications', archive: 'archiveNotifications', tide: 'tideNotifications' }[props.tab],
);

function readNotification(item: Notification, onSuccess?: () => void) {
    router.patch(
        route('notification.update', {
            notification: item.id,
        }),
        {},
        { onSuccess },
    );
}

function readAllNotifications() {
    if (props.tab == 'archive') return;
    router.post(route('notification.readAll'), { tab: props.tab });
}
</script>
<template>
    <v-data-table-virtual
        v-model:page="currentPage"
        :headers="[
            { title: 'Titel', key: 'data.title' },
            { title: 'Am', key: 'created_at' },
            { title: 'Von', key: 'triggered_by' },
            { title: '', key: 'actions', align: 'end', sortable: false },
        ]"
        :items="
            data.map(notification => ({
                ...notification,
                triggered_by: triggeredByUsers.find(user => user.id == notification.data.triggered_by)?.name || 'System',
            }))
        "
        no-data-text="Keine ungelesenen Benachrichtigungen"
    >
        <template v-slot:header.actions v-if="tab != 'archive'">
            <v-btn
                variant="text"
                title="Alle als gelesen markieren"
                color="primary"
                @click.stop="readAllNotifications"
                icon="mdi-close-box-multiple"
            ></v-btn>
        </template>
        <template v-slot:item.created_at="{ item }">
            {{ DateTime.fromISO(item.created_at).toFormat('dd.MM.yyyy HH:mm') }}
        </template>
        <template v-slot:item.actions="{ item }">
            <v-btn
                v-if="tab !== 'archive' && getNotificationUrl(item)"
                color="primary"
                icon="mdi-eye"
                variant="text"
                @click.stop="readNotification(item, () => router.get(getNotificationUrl(item)))"
            >
                <v-icon icon="mdi-eye"></v-icon>
            </v-btn>
            <v-btn v-if="tab !== 'archive'" color="primary" icon="mdi-close" variant="text" @click.stop="readNotification(item)"></v-btn>
        </template>
        <template v-slot:bottom>
            <v-pagination v-if="lastPage > 1" v-model="currentPage" :length="lastPage"></v-pagination>
        </template>
    </v-data-table-virtual>
</template>
