<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
}>();
</script>

<template>
    <v-app>
        <Head :title="title"></Head>
        <div class="d-flex align-center justify-center pa-4" style="min-height: 100vh">
            <img lass="d-none d-md-block" src="/img/auth-mask.png" style="position: absolute; bottom: 0; width: 100%; height: 172px" />
            <v-card class="pa-4 pt-7" width="448">
                <v-card-text>
                    <div class="d-flex justify-center align-center">
                        <v-img src="/img/logo.svg" class="mb-6" style="max-width: 200px"></v-img>
                    </div>
                    <h1 class="text-center font-weight-medium d-flex">
                        <span class="ms-3">{{ $page.props.organization.name }}</span>
                    </h1>
                </v-card-text>

                <v-card-text v-if="$page.props.flash.error">
                    <v-alert type="error" closeable border="start" :key="Math.random()">
                        {{ $page.props.flash.error }}
                    </v-alert>
                </v-card-text>
                <v-card-text v-if="$page.props.flash.success">
                    <v-alert type="success" closeable border="start" :key="Math.random()">
                        {{ $page.props.flash.success }}
                    </v-alert>
                </v-card-text>

                <v-card-text>
                    <slot />
                </v-card-text>
            </v-card>
        </div>
    </v-app>
</template>
