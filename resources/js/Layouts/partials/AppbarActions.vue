<script setup lang="ts">
import { Notification } from '@/types/types';
import { Link, router } from '@inertiajs/vue3';

function readNotification(notification: Notification) {
    router.post(
        route('notification.update', {
            notification: notification.id,
        }),
        {},
        {
            onSuccess: () => {
                if (notification.type == 'App\\Notifications\\PatchNotification')
                    return router.get(
                        route('dispute.index', {
                            openPatch: notification.data.patch_id,
                        }),
                    );
                if (notification.type == 'App\\Notifications\\AbsenceNotification')
                    return router.get(
                        route('dispute.index', {
                            openAbsence: notification.data.absence_id,
                        }),
                    );
            },
        },
    );
}
</script>

<template>
    <v-menu v-if="$page.props.auth.user.unread_notifications.length > 0">
        <template v-slot:activator="{ props }">
            <v-btn color="primary" v-bind="props" stacked>
                <v-badge
                    v-if="$page.props.auth.user.unread_notifications.length > 0"
                    :content="$page.props.auth.user.unread_notifications.length"
                    color="error"
                >
                    <v-icon icon="mdi-bell"></v-icon>
                </v-badge>
                <v-icon v-else icon="mdi-bell"></v-icon>
            </v-btn>
        </template>
        <v-list>
            <v-list-item
                @click.stop="readNotification(notification)"
                v-for="notification in $page.props.auth.user.unread_notifications"
                :key="notification.id"
            >
                <v-list-item-title> {{ notification.data.title }} </v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>

    <Link :href="route('user.profile', { user: $page.props.auth.user.id })">
        <v-btn stacked color="black" prepend-icon="mdi-account" title="Profil" />
    </Link>

    <Link method="post" :href="route('logout')" as="button">
        <v-btn stacked color="black" prepend-icon="mdi-logout" title="Abmelden" />
    </Link>
</template>
