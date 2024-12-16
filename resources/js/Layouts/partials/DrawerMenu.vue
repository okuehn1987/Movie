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
                can('absence', 'viewIndex') && {
                    props: { active: route().current('absence.index'), prependIcon: 'mdi-timer-cancel-outline' },
                    value: route('absence.index'),
                    title: 'Abwesenheiten',
                },
                can('workLog', 'viewIndex') && {
                    props: { active: route().current('workLog.index'), prependIcon: 'mdi-clock-outline' },
                    value: route('workLog.index'),
                    title: 'Arbeitszeiten',
                },
                ...(['organization', 'operatingSite', 'group', 'user', 'timeAccountSetting'].some(m => can(m, 'viewIndex')) ||
                can('organization', 'viewShow')
                    ? [
                          can('organization', 'viewShow') && {
                              title: 'Organisation',
                              subtitle: 'test',
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
                          can('timeAccountSetting', 'viewIndex') && {
                              props: {
                                  active: route().current('timeAccountSetting.index'),
                                  prependIcon: 'mdi-timer',
                              },
                              value: route('timeAccountSetting.index'),
                              title: 'Arbeitszeitkonten',
                          },
                          can('user', 'viewIndex') && {
                              props: { prependIcon: 'mdi-tree', active: route().current('organization.tree') },
                              value: route('organization.tree', { organization: $page.props.organization.id }),
                              title: 'Organigramm',
                          },
                      ]
                    : []),
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
