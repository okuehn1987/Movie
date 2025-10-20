<script setup lang="ts">
import { Notification } from '@/types/types';
import { useNow } from '@/utils';
import { router, usePage } from '@inertiajs/vue3';
import { DateTime } from 'luxon';
import ReportBugDialog from './ReportBugDialog.vue';
import { useDisplay } from 'vuetify';

const display = useDisplay();

function readNotification(notification: Notification) {
    router.post(
        route('notification.update', {
            notification: notification.id,
        }),
    );
}

function openNotification(notification: Notification) {
    let url;
    if (notification.type == 'App\\Notifications\\WorkLogPatchNotification')
        url = route('dispute.index', {
            openPatch: notification.data.work_log_patch_id,
        });
    else if (notification.type == 'App\\Notifications\\WorkLogNotification')
        url = route('dispute.index', {
            openWorkLog: notification.data.work_log_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsenceNotification')
        url = route('dispute.index', {
            openAbsence: notification.data.absence_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsencePatchNotification')
        url = route('dispute.index', {
            openAbsencePatch: notification.data.absence_patch_id,
        });
    else if (notification.type == 'App\\Notifications\\AbsenceDeleteNotification')
        url = route('dispute.index', {
            openAbsenceDelete: notification.data.absence_id,
        });
    else if (notification.type == 'App\\Notifications\\TicketCreationNotification')
        url = route('ticket.index', {
            openTicket: notification.data.ticket_id,
        });
    else if (notification.type == 'App\\Notifications\\TicketUpdateNotification')
        url = route('ticket.index', {
            openTicket: notification.data.ticket_id,
        });
    else if (notification.type == 'App\\Notifications\\TicketFinishNotification')
        url = route('ticket.index', {
            openTicket: notification.data.ticket_id,
        });
    else if (notification.type == 'App\\Notifications\\TicketDeletionNotification')
        url = route('ticket.index', {
            openTicket: notification.data.ticket_id,
        });
    else if (notification.type == 'App\\Notifications\\TicketRecordCreationNotification')
        url = route('ticket.index', {
            openTicket: notification.data.ticket_id,
        });
    else if (notification.type == 'App\\Notifications\\DisputeStatusNotification') {
        if (notification.data.type == 'delete') return;
        if (notification.data.log_model == 'App\\Models\\Absence') url = route('absence.index', { openAbsence: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\AbsencePatch')
            url = route('absence.index', { openAbsencePatch: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\WorkLogPatch')
            url = route('user.workLog.index', { user: usePage().props.auth.user.id, openWorkLogPatch: notification.data.log_id });
        else if (notification.data.log_model == 'App\\Models\\WorkLog')
            url = route('user.workLog.index', { user: usePage().props.auth.user.id, workLog: notification.data.log_id });
    }

    return url && router.get(url, {}, { onSuccess: () => readNotification(notification) });
}
const now = useNow();
function convertTimeStamp(notification: Notification) {
    const endTime = DateTime.fromISO(notification.created_at);
    const diff = now.value.diff(endTime);
    if (diff.as('minutes') < 1) {
        return 'Jetzt';
    }
    if (diff.as('hours') < 1) {
        return 'vor ' + Math.floor(diff.as('minutes')) + ' minuten';
    }
    if (endTime.day !== now.value.day || endTime.month !== now.value.month || endTime.year !== now.value.year) {
        return endTime.toFormat('dd.MM - HH:mm') + ' Uhr';
    }
    return endTime.toFormat('HH:mm') + ' Uhr';
}
// mobile titles for notifications
const disputes = {
    'App\\Notifications\\AbsenceNotification': 'neuer Antrag ',
    'App\\Notifications\\AbsencePatchNotification': 'neue Korrektur',
    'App\\Notifications\\WorkLogPatchNotification': 'neue Korrektur',
    'App\\Notifications\\DisputeStatusNotification': 'Antrag bearbeitet',
    'App\\Notifications\\AbsenceDeleteNotification': 'neuer Antrag',
    'App\\Notifications\\WorkLogNotification': 'neue Buchung',
    'App\\Notifications\\TicketCreationNotification': 'neues Ticket',
    'App\\Notifications\\TicketUpdateNotification': 'Ticket Update',
    'App\\Notifications\\TicketFinishNotification': 'Ticket abgeschlossen',
    'App\\Notifications\\TicketDeletionNotification': 'Ticket gelöscht',
    'App\\Notifications\\TicketRecordCreationNotification': 'Ticket Update',
} satisfies Record<Notification['type'], string>;
</script>

<template>
    <v-menu v-if="$page.props.unreadNotifications.length > 0" id="notification-menu">
        <template v-slot:activator="{ props }">
            <v-btn color="primary" v-bind="props" stacked>
                <v-badge :content="$page.props.unreadNotifications.length" color="error">
                    <v-icon icon="mdi-bell"></v-icon>
                </v-badge>
            </v-btn>
        </template>
        <v-list min-width="200px" @click.stop="() => {}">
            <template
                v-for="(notification, index) in $page.props.unreadNotifications.toSorted((a, b) => b.created_at.localeCompare(a.created_at))"
                :key="notification.id"
            >
                <v-divider v-if="index != 0"></v-divider>
                <v-list-item>
                    <v-list-item-title>
                        <div v-if="display.smAndDown.value">{{ disputes[notification.type] }}</div>
                        <div v-else>{{ notification.data.title }}</div>
                        <div>
                            <sub>{{ convertTimeStamp(notification) }}</sub>
                        </div>
                    </v-list-item-title>
                    <template #append>
                        <div class="ms-md-4 ms-2 ga-md-4 ga-2 d-flex">
                            <v-divider vertical></v-divider>
                            <v-btn
                                :size="display.smAndDown.value ? 'small' : undefined"
                                v-if="
                                    notification.type != 'App\\Notifications\\TicketDeletionNotification' &&
                                    (notification.type != 'App\\Notifications\\DisputeStatusNotification' ||
                                        (notification.type == 'App\\Notifications\\DisputeStatusNotification' && notification.data.type != 'delete'))
                                "
                                color="primary"
                                icon="mdi-eye"
                                variant="text"
                                @click.stop="openNotification(notification)"
                            ></v-btn>
                            <div v-else style="width: 48px"></div>
                            <v-btn
                                :size="display.smAndDown.value ? 'small' : undefined"
                                color="primary"
                                icon="mdi-close"
                                variant="text"
                                @click.stop="readNotification(notification)"
                            ></v-btn>
                        </div>
                    </template>
                </v-list-item>
            </template>
        </v-list>
    </v-menu>
    <v-menu :location="display.smAndDown.value ? 'bottom left' : 'bottom'">
        <template #activator="{ props: activatorProps }">
            <v-btn stacked color="black" prepend-icon="mdi-account-details" v-bind="activatorProps"></v-btn>
        </template>
        <v-list min-width="200px">
            <v-list-item @click.stop="router.get(route('user.profile', { user: $page.props.auth.user.id }))">
                <v-icon icon="mdi-account" class="me-2"></v-icon>
                Profil
            </v-list-item>
            <v-list-item @click.stop="() => {}">
                <ReportBugDialog></ReportBugDialog>
            </v-list-item>
            <v-list-item @click.stop="router.post(route('logout'))">
                <v-icon icon="mdi-logout" class="me-2"></v-icon>
                Abmelden
            </v-list-item>
        </v-list>
    </v-menu>
</template>
<style scoped>
#notification-menu :deep(.v-overlay__content) {
    left: unset !important;
    right: 0 !important;
}
</style>
