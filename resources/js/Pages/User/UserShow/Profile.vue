<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UserShowNavBar from './partial/UserShowNavBar.vue';
import { User } from '@/types/types';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
    user: User;
}>();
</script>
<template>
    <AdminLayout :title="user.first_name + ' ' + user.last_name">
        <UserShowNavBar :user tab="profile" />
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
