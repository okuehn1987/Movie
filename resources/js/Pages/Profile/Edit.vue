<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <AdminLayout title="Profil" tooltip="Profile">
        <v-row>
            <v-col cols="12" sm="6">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
            </v-col>
            <v-col cols="12" sm="6">
                <UpdatePasswordForm />
            </v-col>
        </v-row>
    </AdminLayout>
</template>
