<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Link :href="route('dashboard')">
        <v-btn stacked color="black" title="Notifications">
            <v-badge v-if="page.props.auth.user.notificationCount > 0" color="error" :content="page.props.auth.user.notificationCount">
                <v-icon class="mdi mdi-bell"></v-icon>
            </v-badge>
            <v-icon v-else icon="mdi-bell"></v-icon>
        </v-btn>
    </Link>

    <Link :href="route('profile.edit')">
        <v-btn stacked color="black" prepend-icon="mdi-account" title="Profil" />
    </Link>

    <Link method="post" :href="route('logout')" as="button">
        <v-btn stacked color="black" prepend-icon="mdi-logout" title="Abmelden" />
    </Link>
</template>
