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
            <v-card class="pa-4 pt-7" width="448">
                <v-card-text>
                    <h1 class="text-center font-weight-medium"><v-icon icon="mdi-timer-lock" /><span class="ms-3">ShiftButler</span></h1>
                </v-card-text>

                <v-card-text v-if="$page.props.flash.error">
                    <v-alert type="error" variant="tonal" closeable border="start">
                        {{ $page.props.flash.error }}
                    </v-alert>
                </v-card-text>
                <v-card-text v-if="$page.props.flash.success">
                    <v-alert type="success" variant="tonal" closeable border="start">
                        {{ $page.props.flash.success }}
                    </v-alert>
                </v-card-text>

                <v-card-text>
                    <slot />
                </v-card-text>
            </v-card>
            <img lass="d-none d-md-block" src="/img/auth-mask.png" style="position: absolute; bottom: 0; width: 100%; height: 172px" />
        </div>
    </v-app>
</template>
