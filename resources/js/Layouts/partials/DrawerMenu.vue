<script setup lang="ts">
import { router } from '@inertiajs/vue3';
</script>

<template>
    <v-list
        nav
        @click:select="route => router.get(route.id as string)"
        :items="
            [
                { props: { active: route().current('dashboard'), prependIcon: 'mdi-view-dashboard' }, value: route('dashboard'), title: 'Dashboard' },
                ...($page.props.currentAppModule == 'tide'
                    ? [
                          can('workLog', 'viewIndex') && {
                              props: { active: route().current('workLog.index'), prependIcon: 'mdi-clock-outline' },
                              value: route('workLog.index'),
                              title: 'Arbeitszeiten',
                          },
                      ]
                    : []),
                ...($page.props.currentAppModule == 'flow'
                    ? [
                          can('customer', 'viewIndex') && {
                              props: { active: route().current('customer.index'), prependIcon: 'mdi-folder-account-outline' },
                              value: route('customer.index'),
                              title: 'Kundenliste',
                          },
                          {
                              props: { active: route().current('ticket.index'), prependIcon: 'mdi-ticket-account' },
                              value: route('ticket.index'),
                              title: 'Tickets',
                          },
                      ]
                    : []),
                can('dispute', 'viewIndex') && {
                    props: { active: route().current('dispute.index'), prependIcon: 'mdi-bookmark-outline' },
                    value: route('dispute.index'),
                    title: 'Anträge',
                },
                can('absence', 'viewIndex') && {
                    props: { active: route().current('absence.index'), prependIcon: 'mdi-timer-cancel-outline' },
                    value: route('absence.index'),
                    title: 'Abwesenheiten',
                },
                can('organization', 'viewShow') && {
                    title: 'Organisation',
                    value: route('organization.show', { organization: $page.props.organization.id }),
                    props: { prependIcon: 'mdi-domain', active: route().current('organization.show') },
                },
                can('operatingSite', 'viewIndex') && {
                    props: { active: route().current('operatingSite.index'), prependIcon: 'mdi-map-marker' },
                    value: route('operatingSite.index'),
                    title: 'Betriebsstätten',
                },
                can('group', 'viewIndex') && {
                    props: { active: route().current('group.index'), prependIcon: 'mdi-dots-circle' },
                    value: route('group.index'),
                    title: 'Abteilungen',
                },
                can('user', 'viewIndex') && {
                    props: { active: route().current('user.index'), prependIcon: 'mdi-account-group' },
                    value: route('user.index'),
                    title: 'Mitarbeitende',
                },
                can('user', 'viewIndex') && {
                    props: { prependIcon: 'mdi-tree', active: route().current('organization.tree') },
                    value: route('organization.tree', { organization: $page.props.organization.id }),
                    title: 'Organigramm',
                },
                ...(can('organization', 'viewIndex')
                    ? [
                          { type: 'divider' },
                          { type: 'subheader', title: 'Super-Admin' },
                          {
                              props: { prependIcon: 'mdi-security', active: route().current('organization.index') },
                              value: route('organization.index'),
                              title: 'Organisationen',
                          },
                      ]
                    : []),
            ].filter(Boolean)
        "
    />
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
