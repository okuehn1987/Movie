<script setup lang="ts">
import { router } from '@inertiajs/vue3';
</script>

<template>
    <v-list
        nav
        @click:select="route => router.get(route.id as string)"
        :items="[
            { props: { active: route().current('dashboard'), prependIcon: 'mdi-view-dashboard' }, value: route('dashboard'), title: 'Dashboard' },
            { props: { active: route().current('movies.index'), prependIcon: 'mdi-play' }, value: route('movies.index'), title: 'Movie' },

            ...($page.props.auth.user.is_admin
                ? [
                      {
                          props: { active: route().current('movie.create'), prependIcon: 'mdi-cloud-upload' },
                          value: route('movie.create'),
                          title: 'Add Movies ',
                      },
                      {
                          props: { active: route().current('actor.index'), prependIcon: 'mdi-circle' },
                          value: route('actor.index'),
                          title: 'Actors ',
                      },
                      {
                          props: { active: route().current('actor.create'), prependIcon: 'mdi-circle' },
                          value: route('actor.create'),
                          title: 'Add Actors ',
                      },
                  ]
                : []),
        ]"
    />
</template>

<style scoped>
.v-bottom-navigation {
    position: static !important;
}
</style>
