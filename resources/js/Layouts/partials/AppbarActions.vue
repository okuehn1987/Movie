<script setup lang="ts">
import { Notification } from '@/types/types';
import { Link, router, usePage } from '@inertiajs/vue3';

function readNotification(notification: Notification) {
    router.post(
        route('notification.update', {
            notification: notification.id,
        }),
    );
}

function openNotification(notification: Notification) {
    readNotification(notification);
    let data;
    if (notification.type == 'App\\Notifications\\WorkLogPatchNotification')
        data = route('dispute.index', {
            openPatch: notification.data.work_log_patch_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsenceNotification')
        data = route('dispute.index', {
            openAbsence: notification.data.absence_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsencePatchNotification')
        data = route('dispute.index', {
            openAbsencePatch: notification.data.absence_patch_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsenceDeleteNotification')
        data = route('dispute.index', {
            openAbsenceDelete: notification.data.absence_id,
        });
    else if (notification.type == 'App\\Notifications\\DisputeStatusNotification') {
        if (notification.data.type == 'delete') return;
        if (notification.data.log_model == 'App\\Models\\Absence') data = route('absence.index', { openAbsence: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\AbsencePatch')
            data = route('absence.index', { openAbsencePatch: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\WorkLogPatch')
            data = route('user.workLog.index', { user: usePage().props.auth.user.id, openWorkLogPatch: notification.data.log_id });
    }

    return data && router.get(data);
}
</script>

<template>
    <v-menu v-if="$page.props.auth.user.unread_notifications.length > 0">
        <template v-slot:activator="{ props }">
            <v-btn color="primary" v-bind="props" stacked>
                <v-badge :content="$page.props.auth.user.unread_notifications.length" color="error">
                    <v-icon icon="mdi-bell"></v-icon>
                </v-badge>
            </v-btn>
        </template>
        <v-list>
            <template v-for="(notification, index) in $page.props.auth.user.unread_notifications" :key="notification.id">
                <v-divider v-if="index != 0"></v-divider>
                <v-list-item>
                    <v-list-item-title>{{ notification.data.title }}</v-list-item-title>

                    <template v-slot:append>
                        <div class="ms-4 ga-4 d-flex">
                            <v-divider vertical></v-divider>
                            <v-btn
                                v-if="
                                    (notification.type != 'App\\Notifications\\DisputeStatusNotification' ||
                                        (notification.type == 'App\\Notifications\\DisputeStatusNotification' &&
                                            notification.data.type != 'delete')) &&
                                    notification.type !== 'App\\Notifications\\AbsenceDeleteNotification'
                                "
                                color="primary"
                                icon="mdi-eye"
                                variant="tonal"
                                @click.stop="openNotification(notification)"
                            ></v-btn>
                            <div v-else style="width: 48px"></div>
                            <v-btn color="error" icon="mdi-delete" variant="tonal" @click.stop="readNotification(notification)"></v-btn>
                        </div>
                    </template>
                </v-list-item>
            </template>
        </v-list>
    </v-menu>

    <Link :href="route('user.profile', { user: $page.props.auth.user.id })">
        <v-btn stacked color="black" prepend-icon="mdi-account" title="Profil" />
    </Link>

    <Link method="post" :href="route('logout')" as="button">
        <v-btn stacked color="black" prepend-icon="mdi-logout" title="Abmelden" />
    </Link>
</template>
